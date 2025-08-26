const Jimp = require('jimp');
const { createConnection } = require('../../db');
const { createPdoConnection } = require('../../dbPDO');
const moment = require('../../momentConfig');
async function treatRecognitions(filters){

    let connection;
    let connectionPdo;

    try {

        connection = await createConnection();
        if (!connection) {
            return {'status': false, 'msg': 'sem conexão com o banco Local'};
        }

        connectionPdo = await createPdoConnection();
        if (!connectionPdo) {
            return {'status': false, 'msg': 'sem conexão com o banco PDO'};
        }

        const [users] = await connection.execute(`SELECT cads.controle_acesso_id, cads.ds
            FROM controle_acessos_ds cads 
            JOIN controle_acessos ca ON ca.id = cads.controle_acesso_id 
            WHERE cads.position LIKE 'pic_%' AND ca.ATIVO = 1 AND ca.user_type = 1`);
            

        if(users.length === 0){
            return {'status': false, 'msg': 'sem usuários'};
        }

        const labeledDescriptors = await createFaceMatchers(users);

        if(!labeledDescriptors){
            return {'status': false, 'msg': 'erro ao criar FaceMatcher com cadastrados'};
        }

        const isSinteico = Object.keys(filters).includes('viagensFace');

        const findViagens = isSinteico ?  Object.values(filters.viagensFace) : await getViagens(filters, connectionPdo);
        const grupoConfirm = filters.grupoConfirm;
        const previsto = filters.previsto;
        const matricula = filters.matricula;
        const grupo = filters.grupo;
        const todosGrupos = filters.todosGrupos;
        let allData = {};
        let embCad = {};
        if(findViagens.status || isSinteico){

            const viagens = isSinteico ? findViagens : findViagens.viagens;
           
            for(const viagem of viagens){

                const findEmbs = await getEmbarques(viagem, connection, connectionPdo, grupoConfirm, previsto, matricula, grupo, todosGrupos, labeledDescriptors, isSinteico);

                if(!findEmbs.status){
                    continue;
                }

                const ITIREALIZADOOK = `${viagem.NOMELINHA} - ${viagem.DESCRICAO}`;
                const IDVIAGEM = viagem.IDVIAGEM;
                const DATAREALIZADO = viagem.DATAINIREAL ? moment.utc(viagem.DATAINIREAL).format('DD/MM/YYYY HH:mm:ss') : '-';

                if (!allData[IDVIAGEM]) {
                    allData[IDVIAGEM] = {};
                }

                if (!embCad[IDVIAGEM]) {
                    embCad[IDVIAGEM] = {
                        'embarcados': viagem.embarcados ?? 0,
                        'cadastrados': viagem.cadastrados ?? 0
                    };
                }

                for(const emb of findEmbs.embs){
                    const CA = emb.controle_acesso_id;
                    if (!allData[IDVIAGEM][CA]) {
                        allData[IDVIAGEM][CA] = {
                            'PREF': viagem.PREFIXOVEIC,
                            'PLACA': viagem.PLACA,
                            'GRUPO': emb.GRUPO,
                            'CODIGO': '',
                            'NOME': emb.NOME,
                            'MATRICULA': emb.MATRICULA,
                            'STATUS': emb.STATUSCAD,
                            'ITIDAPREV': emb.ITIDAPREV,
                            'ITVOLTAPREV': emb.ITVOLTAPREV,
                            'DATAREALIZADO': DATAREALIZADO,
                            'SENTREALIZADO': viagem.SENTIDO,
                            'ITIREALIZADOOK': ITIREALIZADOOK,
                            'PREVOK': emb.PREVOK,
                            'LATITUDEEMB': 0,
                            'LONGITUDEEMB': 0,
                            'PONTOREFEREMB': '',
                            'HORAMARCACAOEMB': '',
                            'LOGRADOUROEMB': '',
                            'LOCALIZACAOEMB': '',
                            'IMGS': {
                                'emb': '',
                                'demb': ''
                            },
                            'LATITUDEDESEMB': 0,
                            'LONGITUDEDESEMB': 0,
                            'PONTOREFERDESEMB': '',
                            'HORAMARCACAODESEMB': '',
                            'LOGRADOURODESEMB': '',
                            'LOCALIZACAODESEMB': '',
                            'EMBSORT': emb.real_time
                        }

                        embCad[IDVIAGEM].embarcados++;

                        if(emb.PREVOK === 'PREV'){
                            embCad[IDVIAGEM].cadastrados++;
                        }
                    }

                    allData[IDVIAGEM][CA][`LATITUDE${emb.isEmb ? 'EMB' : 'DESEMB'}`] = emb.LATITUDE;
                    allData[IDVIAGEM][CA][`LONGITUDE${emb.isEmb ? 'EMB' : 'DESEMB'}`] = emb.LONGITUDE;
                    allData[IDVIAGEM][CA][`PONTOREFER${emb.isEmb ? 'EMB' : 'DESEMB'}`] = emb.pontoRefNome;
                    allData[IDVIAGEM][CA][`HORAMARCACAO${emb.isEmb ? 'EMB' : 'DESEMB'}`] = emb.time_print;
                    allData[IDVIAGEM][CA][`LOGRADOURO${emb.isEmb ? 'EMB' : 'DESEMB'}`] = emb.pontoRefLogra;
                    allData[IDVIAGEM][CA][`LOCALIZACAO${emb.isEmb ? 'EMB' : 'DESEMB'}`] = emb.pontoRefLoc;

                    if(emb.img){
                        allData[IDVIAGEM][CA]['IMGS'][`${emb.isEmb ? 'emb' : 'demb'}`] = {
                            'recid': emb.id,
                            'img': emb.img
                        };
                    }
                    
                    
                }
                
            }

            return {'status': true, 'allData': allData, 'embCad': embCad};

        }else{

            return {'status': findViagens.status, 'msg': findViagens.msg};

        }
        
    } catch (error) {
        console.error('Erro ao tratar reconhecimentos:', error.message);
        return {'status': false, 'msg': 'Erro ao tratar reconhecimentos'};
    } finally {
        if (connection) await connection.end();
        if (connectionPdo) await connectionPdo.close();
    }
}

async function createFaceMatchers(descriptors, notRecognized = false, allRecognized = false){
    
    const labeledDescriptors = [];
    let FaceMatcher = false;

    try {

        for (const ds of descriptors) {

            const label = notRecognized ? ds.id.toString() : ds.controle_acesso_id.toString();
            
            let labeledDescriptor = labeledDescriptors.find(descriptor => descriptor.label === label);
            
            const dsString = ds.ds.toString();
            
            var stringArray = dsString.split(',');
    
            var floatArray = stringArray.map(function(item) {
                return parseFloat(item);
            });
    
            var descriptorOK = new Float32Array(floatArray);
    
            if (!labeledDescriptor) {
                labeledDescriptor = new faceapi.LabeledFaceDescriptors(label, [descriptorOK]);
                labeledDescriptors.push(labeledDescriptor);
            }else{
                labeledDescriptor.descriptors.push(descriptorOK);
            }

            if(labeledDescriptors.length > 0 && (notRecognized || allRecognized)){
                FaceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.4);
            }
    
        }
        
    } catch (error){
        console.log(error);
    }

    if(notRecognized || allRecognized){
        return FaceMatcher;
    }

    return labeledDescriptors.length > 0 ? labeledDescriptors : false;
}

async function getViagens(filters, connectionPdo){

    try {

        let between = '';
        let v = '';
        let lns = '';

        if(filters.v > 0){

            v = `v.ID = ${filters.v}`;

        }else{
            const dateStart = filters.dateStart;
            const dataEnd = filters.dataEnd;
            between = `v.DATAHORA_INICIAL_PREVISTO BETWEEN '${dateStart}' AND '${dataEnd}'`;
        }

        if(filters.lns){
            lns = `AND i.LINHA_ID IN (${filters.lns})`;
        }

        const sql = `SELECT 
                    l.GRUPO_LINHA_ID,
                    v.ID AS IDVIAGEM,
                    l.ID AS IDLINHA,
                    v.ITINERARIO_ID AS IDTINEREAL,
                    l.NOME AS NOMELINHA,
                    gp.NOME AS GRUPO,
                    gp.ID AS GRUPOLINHAID,
                    l.PREFIXO AS PREFIXO,
                    i.TIPO AS TIPO,
                    i.SENTIDO AS SENTIDO,
                    i.TRECHO AS TRECHO,
                    i.DESCRICAO AS DESCRICAO,
                    v.DATAHORA_INICIAL_PREVISTO AS DATAINIPREVISTO,
                    v.DATAHORA_INICIAL_REALIZADO AS DATAINIREAL,
                    v.DATAHORA_FINAL_PREVISTO AS DATAFIMPREV,
                    v.DATAHORA_FINAL_REALIZADO AS DATAFIMREAL,
                    vc.ID AS IDVEIC,
                    vc.PLACA AS PLACA,
                    vc.NOME AS PREFIXOVEIC
            FROM BD_CLIENTE.dbo.VIAGENS v
            JOIN ITINERARIOS i ON i.ID = v.ITINERARIO_ID
            JOIN LINHAS l ON l.ID = i.LINHA_ID
            JOIN VEICULO vc ON vc.ID = v.VEICULO_ID
            JOIN GRUPO_LINHAS gp ON gp.ID = l.GRUPO_LINHA_ID
            WHERE ${between} ${v} ${lns}
            ORDER BY DATAHORA_INICIAL_PREVISTO`;

            const request = connectionPdo.request();
            const result = await request.query(sql);

            if(result.recordset.length > 0){
                return {'status': true, 'viagens': result.recordset};
            }else{
                return {'status': false, 'msg': 'Não foram encontradas viagens.'}
            }
            

    } catch (error) {
        return {'status': false, 'msg': 'Erro ao encontrar viagens.'}
    }

}

async function getEmbarques(viagem, connection, connectionPdo, grupoConfirm, previsto, matricula, grupo, todosGrupos, labeledDescriptors, isSinteico){

    try {

        const IDTINEREAL = viagem.IDTINEREAL;
        const SENTIDO = viagem.SENTIDO;
        const veiculo_id = viagem.IDVEIC;
        const dataIni = moment.utc(viagem.DATAINIREAL ?? viagem.DATAINIPREVISTO).format('YYYY-MM-DD HH:mm:ss');
        const dataFim = moment.utc(viagem.DATAFIMREAL ?? viagem.DATAFIMPREV).format('YYYY-MM-DD HH:mm:ss');
        const ajustHourStart = moment(dataIni).subtract(20, 'minutes').format('YYYY-MM-DD HH:mm:ss');
        const ajustHourEnd = moment(dataFim).add(20, 'minutes').format('YYYY-MM-DD HH:mm:ss');
        const secIni = moment(dataIni).unix();
        const secEnd = moment(dataFim).unix();

        let sqlRecognitions;

        if(!isSinteico){
            
            sqlRecognitions = `SELECT 
            id, ds, controle_acesso_id, real_time, latitude AS LATITUDE, longitude AS LONGITUDE, img
            FROM face_recognitions 
            WHERE veiculo_id = '${veiculo_id}'
            AND real_time BETWEEN '${ajustHourStart}' AND '${ajustHourEnd}'
            AND deleted_at IS NULL
            ORDER BY real_time`;

        }else{

            sqlRecognitions = `SELECT 
            id, ds, controle_acesso_id, real_time, latitude AS LATITUDE, longitude AS LONGITUDE
            FROM face_recognitions 
            WHERE veiculo_id = '${veiculo_id}'
            AND real_time BETWEEN '${ajustHourStart}' AND '${ajustHourEnd}'
            AND deleted_at IS NULL
            ORDER BY real_time`;

        }

        const [recognitions] = await connection.execute(sqlRecognitions);
        
        if(recognitions.length === 0){
            return {'status': false, 'msg': 'Sem reconhecimentos'};
        }

        //1 converte tudo que é necessário para string e os descritores para Float32Array
        recognitions.forEach(rec => {
            const timePrint = moment(rec.real_time).format('DD/MM/YYYY HH:mm:ss');
            const recCa = rec.controle_acesso_id.toString();
            const recRt = moment(rec.real_time).unix();
        
            const dsString = rec.ds.toString();
            const stringArray = dsString.split(',');
        
            const floatArray = stringArray.map(item => parseFloat(item));
            const recDs = new Float32Array(floatArray);

            rec.controle_acesso_id = recCa;
            rec.real_time = recRt;
            rec.time_print = timePrint;
            rec.ds = recDs;
            rec.NOME = 'NÃO RECONHECIDO';
            rec.STATUSCAD = 'SEM CADASTRO';
            rec.MATRICULA = ' - ';
            rec.GRUPO = ' - ';
            rec.PREVOK = 'NREALIZADO';
            rec.ITIDAPREV = '';
            rec.ITVOLTAPREV = '';
            rec.recognized = false;
            rec.isSinteico = isSinteico;
        }); 

        //2 tenta remover os não reconhecidos duplicados por tempo
        const notRecognized = recognitions.filter(item => item.controle_acesso_id === '0');
        let i = 0;    
        while (i < notRecognized.length) {
            const nRec = notRecognized[i];
            const recID = nRec.id;
            const recRt = nRec.real_time;
        
            const similarNotRecognized = notRecognized.some((item, j) => {
                if (i === j) return false;
                const itemTime = item.real_time;
                const timeDifferenceInSeconds = Math.abs(itemTime - recRt);
                return timeDifferenceInSeconds <= 10;
            });
        
            if (similarNotRecognized) {
                
                const indexToRemove = recognitions.findIndex(item => item.id === recID);
                if (indexToRemove !== -1) {
                    recognitions.splice(indexToRemove, 1);
                }
                notRecognized.splice(i, 1);
            } else {
                i++; // Só incrementa o índice se não remove
            }
        }

        //3 tenta reconhecer os não reconhecidos com os cadastrados
        if (labeledDescriptors) {

            let matchMap = {};

            let i = 0;
        
            for(const nRec of notRecognized){
                const recID = nRec.id;
                const recDs = nRec.ds;

                for (const user of labeledDescriptors) {
                    
                    for (const descriptor of user.descriptors) {
                        const faceMatcher = new faceapi.FaceMatcher(descriptor);
                        const bestMatch = faceMatcher.findBestMatch(recDs);
                        
                        if (bestMatch && bestMatch._distance <= 0.4 && bestMatch.label !== 'unknown') {
                            
                            // Atualiza o mapa de correspondência
                            if (!matchMap[recID]) {
                                matchMap[recID] = {};
                            }
                            if (!matchMap[recID][user.label]) {
                                matchMap[recID][user.label] = {
                                    'qtd':0,
                                    'dist':bestMatch._distance
                                };
                            }
                            matchMap[recID][user.label].qtd++;
                            matchMap[recID][user.label].dist = bestMatch._distance < matchMap[recID][user.label].dist ? bestMatch._distance : matchMap[recID][user.label].dist;
                        }
                    }
                }
            }            

            // console.log(matchMap);
            
            const filteredMatchMap = {};

            Object.keys(matchMap).forEach(key => {
                const subObject = matchMap[key];
                const maxQtd = Math.max(...Object.values(subObject).map(o => o.qtd));
                const filteredByQtd = Object.entries(subObject).filter(([_, value]) => value.qtd === maxQtd);
                const filteredByDist = filteredByQtd.filter(([_, value]) => value.dist <= 0.4);
                const selected = filteredByDist.length > 0 
                  ? filteredByDist.sort((a, b) => a[1].dist - b[1].dist)[0]
                  : filteredByQtd.sort((a, b) => a[1].dist - b[1].dist)[0];
                
                filteredMatchMap[key] = { [selected[0]]: selected[1] };
            });

            // console.log(filteredMatchMap);

            for (const [key, value] of Object.entries(filteredMatchMap)) {
                for (const [innerKey, innerValue] of Object.entries(value)) {

                    const indexToUpdate = recognitions.findIndex(item => item.id == key);

                    if (indexToUpdate !== -1 && innerValue.qtd >= 2 && innerValue.dist <= 0.4) {
                        recognitions[indexToUpdate].controle_acesso_id = innerKey;
                    }

                    const indexToRemoveNr = notRecognized.findIndex(item => item.id == key);
                    if (indexToRemoveNr !== -1) {
                        notRecognized.splice(indexToRemoveNr, 1);
                    }
                }
            }
        }
        
        //4 tenta remover reconhecidos duplicados por tempo
        const recognized = recognitions.filter(item => item.controle_acesso_id !== '0');
        let index = 0;
        while (index < recognized.length) {
            const rec = recognized[index];
            const recID = rec.id;
            const recCa = rec.controle_acesso_id;
            const recRt = rec.real_time;

            const similarRecognized = recognized.some((item, j) => {
                if (index === j) return false;
                const itemTime = item.real_time;
                const itemCA = item.controle_acesso_id;
                const timeDifferenceInSeconds = Math.abs(itemTime - recRt);
                return timeDifferenceInSeconds <= 300 && itemCA === recCa;
            });

            if (similarRecognized) {
                const indexToRemove = recognitions.findIndex(item => item.id === recID);

                if (indexToRemove !== -1) {
                    recognitions.splice(indexToRemove, 1);
                }

                recognized.splice(index, 1); // Remove de recognized e não incrementa o índice
            } else {
                index++; // Só incrementa o índice se não remove
            }
        }

        //5 tenta criar correspondência entre os desconhecidos(quando ainda sobrarem ao menos 2 desconhecidos)
        if(notRecognized.length >= 2){
            const notRecMatch = [];
            for(const nRec of notRecognized){
                
                const recID = nRec.id;
                const recRt = nRec.real_time;
                const recDs = nRec.ds;
                
                const otherNotRecognized = notRecognized.filter(item => item.id !== recID);
        
                const notRecognizedFaceMatcher = await createFaceMatchers(otherNotRecognized, true);
                const bestMatch = notRecognizedFaceMatcher.findBestMatch(recDs);
        
                if (bestMatch && bestMatch.label !== 'unknown') {
        
                    const hasMatch = notRecMatch.filter(item => item.int == bestMatch.label);
                    
                    if(hasMatch.length === 1){
                        const findId = hasMatch[0].find.id;
                        const findRt = hasMatch[0].find.real_time;
                        
                        if(recRt >= findRt){
                            const indexToRemove = recognitions.findIndex(item => item.id == findId);
                            if (indexToRemove !== -1) {
                                recognitions.splice(indexToRemove, 1);
                            }
                            const indexToRemoveNr = notRecognized.findIndex(item => item.id == findId);
                            if (indexToRemoveNr !== -1) {
                                notRecognized.splice(indexToRemoveNr, 1);
                            }
                        }
                    }
        
                    notRecMatch.push({
                        'int':recID, 
                        'find':{
                            'id': bestMatch.label, 
                            'real_time': notRecognized.filter(item => item.id == bestMatch.label)[0].real_time
                        }
                    });
        
                    const newRecId = `rec-${recID}-${bestMatch.label}`;
                    const indexToUpdate = recognitions.findIndex(item => item.id === recID);
                    if (indexToUpdate !== -1) {
                        recognitions[indexToUpdate].controle_acesso_id = newRecId;
                    }
                    const indexToUpdate2 = recognitions.findIndex(item => item.id == bestMatch.label);
                    if (indexToUpdate2 !== -1) {
                        recognitions[indexToUpdate2].controle_acesso_id = newRecId;
                    }
        
                    const indexToRemove = notRecognized.findIndex(item => item.id == bestMatch.label);
        
                    if (indexToRemove !== -1) {
                        notRecognized.splice(indexToRemove, 1);
                    }
                }
                
            }
        }

        //6 checar se ainda sobraram não reconhecidos
        if(notRecognized.length > 0){
            
            //se ainda sobram não reconhecidos tenta achar semelhança
            //com os reconhecidos pelo app e tratados anteriomente 
            const allRecognized = recognitions.filter(item => 
                item.controle_acesso_id !== '0' && !item.controle_acesso_id.startsWith('rec-')
            );

            if(allRecognized.length > 0){

                const allRecognizedFaceMatcher = await createFaceMatchers(allRecognized, false, true);

                for(const nRec of notRecognized){

                    const recID = nRec.id;
                    const recDs = nRec.ds;

                    const bestMatch = allRecognizedFaceMatcher.findBestMatch(recDs);

                    if (bestMatch && bestMatch.label !== 'unknown') {
                        const indexToUpdate = recognitions.findIndex(item => item.id === recID);
                        if (indexToUpdate !== -1) {
                            recognitions[indexToUpdate].controle_acesso_id = bestMatch.label;
                        }
        
                        notRecognized.splice(notRecognized.indexOf(nRec), 1);
                    }
                    
                }
                
            }

        }

        //tenta econtrar reconhecidos com mais de 2 para eliminar possíveis 
        //reconhecimentos durante a viagem
        const allRecognized = recognitions.filter(item => 
            item.controle_acesso_id !== '0' && !item.controle_acesso_id.startsWith('rec-')
        );

        const groupedRecognitions = allRecognized.reduce((acc, current) => {
            const key = current.controle_acesso_id;
            if (!acc[key]) {
                acc[key] = [];
            }
            acc[key].push(current);
            return acc;
        }, {});

        //depois de agrupar por controle_acesso_id usa o key que é o controle_acesso_id
        //para pegar os dados do passageiro no banco
        for(const getPax of Object.keys(groupedRecognitions)){
            
            let sqlgetPax;

            if(!isSinteico){

                sqlgetPax = `SELECT 
                CA.CONTROLE_ACESSO_GRUPO_ID AS GRUPOID,
                CASE 
                    WHEN CA.CONTROLE_ACESSO_GRUPO_ID IN (${grupoConfirm}) THEN CA.MATRICULA_FUNCIONAL
                    ELSE 'De Outro Grupo'
                END AS MATRICULA, 
                CASE 
                    WHEN CA.CONTROLE_ACESSO_GRUPO_ID IN (${grupoConfirm}) THEN CA.NOME
                    ELSE 'De Outro Grupo'
                END AS NOME, 
                CA.ATIVO,
                CASE
                    WHEN ${SENTIDO} = 0 AND CA.ITINERARIO_ID_IDA = ${IDTINEREAL} THEN 'PREV'
                    WHEN ${SENTIDO} = 1 AND CA.ITINERARIO_ID_VOLTA = ${IDTINEREAL} THEN 'PREV'
                    WHEN ${SENTIDO} = 0 AND CA.ITINERARIO_ID_IDA != ${IDTINEREAL} THEN 'NPREV'
                    WHEN ${SENTIDO} = 1 AND CA.ITINERARIO_ID_VOLTA != ${IDTINEREAL} THEN 'NPREV'
                    ELSE 'NREALIZADO'
                END AS PREVOK,
                CASE 
                    WHEN CA.CONTROLE_ACESSO_GRUPO_ID IN (${grupoConfirm}) THEN CAG.NOME
                    WHEN CA.CONTROLE_ACESSO_GRUPO_ID IS NULL THEN ' - '
                    ELSE 'De Outro Grupo'
                END AS GRUPO,
                CASE 
                    WHEN CA.CONTROLE_ACESSO_GRUPO_ID IN (${grupoConfirm}) THEN 1
                    ELSE 0
                END AS INGRUPO,
                ITIIDA.DESCRICAO AS ITIDAPREV,
                ITVOLTA.DESCRICAO AS ITVOLTAPREV,
                LIIDA.NOME AS LIIDANOME,
                LIVOLTA.NOME AS LIVOLTANOME
                FROM controle_acessos CA
                LEFT JOIN acesso_grupos CAG ON CAG.ID_ORIGIN = CA.CONTROLE_ACESSO_GRUPO_ID
                LEFT JOIN itinerarios ITIIDA ON ITIIDA.ID_ORIGIN = CA.ITINERARIO_ID_IDA OR ITIIDA.ITINERARIO_ID_PAI = CA.ITINERARIO_ID_IDA
                LEFT JOIN itinerarios ITVOLTA ON ITVOLTA.ID_ORIGIN = CA.ITINERARIO_ID_VOLTA OR ITVOLTA.ITINERARIO_ID_PAI = CA.ITINERARIO_ID_VOLTA
                LEFT JOIN linhas LIIDA ON LIIDA.ID_ORIGIN = ITIIDA.LINHA_ID
                LEFT JOIN linhas LIVOLTA ON LIVOLTA.ID_ORIGIN = ITVOLTA.LINHA_ID
                WHERE CA.id = ${getPax}`;

            }else{

                sqlgetPax = `SELECT
                CASE
                    WHEN ${SENTIDO} = 0 AND CA.ITINERARIO_ID_IDA = ${IDTINEREAL} THEN 'PREV'
                    WHEN ${SENTIDO} = 1 AND CA.ITINERARIO_ID_VOLTA = ${IDTINEREAL} THEN 'PREV'
                    WHEN ${SENTIDO} = 0 AND CA.ITINERARIO_ID_IDA != ${IDTINEREAL} THEN 'NPREV'
                    WHEN ${SENTIDO} = 1 AND CA.ITINERARIO_ID_VOLTA != ${IDTINEREAL} THEN 'NPREV'
                    ELSE 'NREALIZADO'
                END AS PREVOK
                FROM controle_acessos CA
                WHERE CA.id = ${getPax}`;

            }

            const [pax] = await connection.execute(sqlgetPax);

            if (pax.length === 1) {
                
                const recognitionsWithSamePax = recognitions.filter(recognition => recognition.controle_acesso_id === getPax);

                if(!isSinteico){
                    const STATUSCAD = pax[0].ATIVO === 1 ? 'ATIVO' : 'INATIVO';

                    let ITIDAPREV = '';
                    if(pax[0].ITIDAPREV){
                        ITIDAPREV = pax[0].ITIDAPREV.replace(/Âº/g, 'º').replace(/Â°/g, 'º');
                        if(pax[0].LIIDANOME){
                            ITIDAPREV = `${pax[0].LIIDANOME} - ${ITIDAPREV}`;
                        }
                    }

                    let ITVOLTAPREV = '';
                    if(pax[0].ITVOLTAPREV){
                        ITVOLTAPREV = pax[0].ITVOLTAPREV.replace(/Âº/g, 'º').replace(/Â°/g, 'º');
                        if(pax[0].LIVOLTANOME){
                            ITVOLTAPREV = `${pax[0].LIVOLTANOME} - ${ITVOLTAPREV}`;
                        }
                    }

                    recognitionsWithSamePax.forEach(recognition => {
                    
                        recognition.recognized = true;
                        recognition.NOME = pax[0].NOME;
                        recognition.STATUSCAD = STATUSCAD;
                        recognition.MATRICULA = pax[0].MATRICULA;
                        recognition.GRUPO = pax[0].GRUPO;
                        recognition.GRUPOID = pax[0].GRUPOID;
                        recognition.PREVOK = pax[0].PREVOK;
                        recognition.ITIDAPREV = ITIDAPREV;
                        recognition.ITVOLTAPREV = ITVOLTAPREV;
                        recognition.INGRUPO = pax[0].INGRUPO === 1 ? true : false;
                        
                    });

                }else{

                    recognitionsWithSamePax.forEach(recognition => {
                    
                        recognition.recognized = true;
                        recognition.PREVOK = pax[0].PREVOK;
                        
                    });

                } 
                
            }
        }  

        const minMaxIds = Object.values(groupedRecognitions).flatMap(group => {
            if (group.length <= 2) {
                return group.map(item => item.id);
            }
            const sortedGroup = group.sort((a, b) => a.real_time - b.real_time);
            return [sortedGroup[0].id, sortedGroup[sortedGroup.length - 1].id];
        });


        let recsok = recognitions.filter(item => 
            minMaxIds.includes(item.id) || item.controle_acesso_id === '0'
        ).map(({ ds, ...resto }) => resto);

        //aplicar filtros

        //previsto
        if(previsto){
            recsok = recsok.filter(item => 
                (previsto === '1' && item.PREVOK === 'PREV') || (previsto === '2' && item.PREVOK === 'NPREV')
            ); 
        }

        //matricula
        if(matricula){
            recsok = recsok.filter(item => item.MATRICULA === matricula); 
        }

        //grupo e todosGrupos
        if(grupo && !todosGrupos){

            grupo = grupo.split(",");
            recsok = recsok.filter(item => grupo.includes(item.GRUPOID.toString())); 

        }

        let indexR = 0;
        for(const rec of recsok){
            
            let isEmb = true;

            rec.controle_acesso_id = rec.controle_acesso_id === '0' ? indexR : rec.controle_acesso_id;

            if(!isSinteico){
                
                const diffIni = Math.floor(Math.abs(rec.real_time - secIni) / 60);
                const diffEnd = Math.floor(Math.abs(rec.real_time - secEnd) / 60);
                
                
                //se for volta considera embarque até 10 minutos após o início
                if(SENTIDO == 1){
                    isEmb = (diffIni <= 10);
                }else{
                    //se for ida considera embarque somente até 15 minutos do final
                    isEmb = (diffEnd >= 15);
                }

                const {pontoRefNome, pontoRefLogra, pontoRefLoc} = await getPontoRef(rec.LATITUDE, rec.LONGITUDE, connectionPdo);

                rec.pontoRefNome = pontoRefNome;
                rec.pontoRefLogra = pontoRefLogra;
                rec.pontoRefLoc = pontoRefLoc;

                const img = (!rec.recognized || rec.INGRUPO) ? await imgToShow(rec.img) : false;

                rec.img = img;
            }

            rec.isEmb = isEmb;

            indexR++;
        }
        
        return {'status': true, 'embs': recsok};
        
    } catch (error) {
        return {'status': false, 'msg': 'Erro ao carregar reconhecimentos'};
    }

}

async function getPontoRef(latitude, longitude, connectionPdo){

    try {

        const sql = `
            SELECT TOP 1 ID, NOME, LOGRADOURO, LOCALIZACAO, LATITUDE, LONGITUDE
            FROM (
                SELECT *,
                    (
                        3960 * acos(
                            cos(radians(@latitude)) *
                            cos(radians(LATITUDE)) *
                            cos(radians(LONGITUDE) - radians(@longitude)) +
                            sin(radians(@latitude)) *
                            sin(radians(LATITUDE))
                        )
                    ) AS Distance
                FROM PONTOS_REFERENCIA
            ) AS T
            WHERE T.Distance < @distance
            ORDER BY T.Distance ASC`;

        const request = connectionPdo.request();
        request.input('latitude', latitude);
        request.input('longitude', longitude);
        request.input('distance', 0.5);

        const result = await request.query(sql);

        if(result.recordset.length === 1){
            return {
                'pontoRefNome': result.recordset[0].NOME ?? '',
                'pontoRefLogra': result.recordset[0].LOGRADOURO ?? '',
                'pontoRefLoc': result.recordset[0].LOCALIZACAO ?? ''
            };
        }

        return {
            'pontoRefNome': '',
            'pontoRefLogra':'',
            'pontoRefLoc': ''
        };
        
    } catch (error) {
       
        console.error('Erro ao executar a consulta:', error.message);
        return {
            'pontoRefNome': '',
            'pontoRefLogra':'',
            'pontoRefLoc': ''
        };
    }

}

async function imgToShow(blobImage) {

    try {
        
        let image = await Jimp.read(blobImage);
    
        const base64Image = await image.getBase64Async(Jimp.MIME_JPEG);

        image = null;
    
        return base64Image;
        
    } catch (error) {
        return false;
    }
   
}

module.exports = {
    treatRecognitions: treatRecognitions
};