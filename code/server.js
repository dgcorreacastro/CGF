// server.js
require('dotenv').config();
const allowOrigin = process.env.ENV === '1' ? process.env.ALLOW_ORIGIN : process.env.ALLOW_ORIGIN_DEV;
const { treatRecognitions } = require('./assets/js/treatRecognitions');
const express = require('express');

const app = express();
const port = 3000;

faceapi = require('./assets/js/face-api.min.js');
        
modelsPath = './assets/js/models';

app.use(express.json());

let modelsLoaded = false;

const loadModels = async () => {
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromDisk(modelsPath),
            faceapi.nets.tinyFaceDetector.loadFromDisk(modelsPath),
            faceapi.nets.faceLandmark68Net.loadFromDisk(modelsPath),
            faceapi.nets.faceRecognitionNet.loadFromDisk(modelsPath)
        ]);
        modelsLoaded = true;
        console.log('Models loaded successfully');
    } catch (error) {
        console.error('Error loading models:', error);
    }
};

const initializeServer = async () => {

    await loadModels();

    app.options('/treatRecognitions', (req, res) => {
        res.header('Access-Control-Allow-Origin', allowOrigin);
        res.header('Access-Control-Allow-Methods', 'POST');
        res.header('Access-Control-Allow-Headers', 'Content-Type');
        res.send();
    });

    app.post('/treatRecognitions', async (req, res) => {
        if (!modelsLoaded) {
            return res.status(503).json({ status: false, msg: 'Models are not loaded yet. Please try again later.' });
        }

        try {
            const filters = req.body.data;
            const result = await treatRecognitions(filters);
            res.header('Access-Control-Allow-Origin', allowOrigin);
            res.json(result);
        } catch (error) {
            res.status(500).json({ status: false, msg: error.message });
        }
    });
    
    app.get('*', (req, res) => {
        res.redirect(''); // TODO: POPULATE WITH INDEX URL
    });
    
    app.listen(port, () => {
        console.log(`Servidor rodando na porta ${port}: ${process.env.ENV === '1' ? 'Produção' : 'Dev'}`);
    });

};

initializeServer();