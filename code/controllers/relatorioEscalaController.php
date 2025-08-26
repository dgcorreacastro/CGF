<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class relatorioEscalaController extends controller 
{

    public $array = array(1 => "Janeiro", 2 => "Fevereiro", 3 => "Março", 4 => "Abril", 5 => "Maio", 6 => "Junho", 7 => "Julho", 8 => "Agosto", 9 => "Setembro", 10 => "Outubro", 11 => "Novembro", 12 => "Dezembro");

	public function index()
	{
        $data               = array();
        // $escala         = new EscalaTrabalho();
        // $dataRet        = $escala->list("2, 3", $pag, $limit, $unid); // "2, 3" array status
		// $data['ret']    = $dataRet['escalas'];
        // $data['ttPages']= $dataRet['total'];
        $nowY               = date("Y");
        $ano                = array( ($nowY - 1), $nowY, ($nowY + 1) );

        $users 	            = new UserEscala();
		$data['unidades']   = $users->getUnidades();
        $data['users']      = $users->getLideres(); 
        $data['meses']      = $this->array;
        $data['ano']        = $ano;
    
		$this->loadTemplate('escalaTrabalho/relatorioEscala/index', $data);
		exit();
	}

    private function getLetter($day, $mon, $year)
    {
        $d          = " - ";
        $descrDa    = date("D", strtotime($year .'-'. $mon .'-'. $day));

        switch ($descrDa) {
            case 'Sun': $d = "D"; break;
            case 'Mon': $d = "S"; break;
            case 'Tue': $d = "T"; break;
            case 'Wed': $d = "Q"; break;
            case 'Thu': $d = "Q"; break;
            case 'Fri': $d = "S"; break;
            case 'Sat': $d = "S"; break;
        }

        return $d;
    }

    public function gerar()
    {
        if ( !isset($_POST['tipo']) || $_POST['tipo'] == "" )
        {
            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
            header("Location: " . BASE_URL . "relatorioEscala/");
        }

        if($_POST['tipo'] == 1) // Gerar SAP
            $this->generateSap( $_POST['mes'],  $_POST['ano'], $_POST['unid'], $_POST['gestor'] );
        else if($_POST['tipo'] == 2) // Gerar restaurante
            $this->printRes( $_POST['mes'],  $_POST['ano'], $_POST['unid'], $_POST['gestor'] );
        else if($_POST['tipo'] == 3){ // Gerar Fretamento - Taipastur
            $this->generateFretamento($_POST['mes'],  $_POST['ano'], $_POST['unid'], $_POST['gestor']);
        } else {
            $_SESSION['merr'] = "Ocorreu um erro, tente novamente!";
            header("Location: " . BASE_URL . "relatorioEscala/");
            exit;
        }

    }

    private function generateSap( $mes, $ano, $unid, $gestor )
    {
        $escalaTrab     = new EscalaTrabalho();
        $dtEscala       = $escalaTrab->getSapData($mes, $ano, $unid, $gestor);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

    
        $l = 1;
        foreach($dtEscala as $esc)
        {
            if ( isset( $esc['re'] ) )
            {
                for( $i=1; $i <= 31; $i++)
                {
                    if ( isset( $esc['t'.$i] ) && $esc['t'.$i] == 1 ) // Se tiver em folga
                    {
                        $sheet->setCellValue('A'.$l, $esc['re'] )
                                ->getStyle('A'.$l)
                                ->getBorders()
                                ->getOutline()
                                ->setBorderStyle(Border::BORDER_THIN)
                                ->setColor(new Color('000000'));

                        $sheet->getStyle('A'.$l)
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        $dt = ( $i < 10 ? "0".$i : $i ) . "." . ( $mes < 10 ? "0".$mes : $mes ) . "." . $ano;

                        $sheet->setCellValue('B'.$l, $dt)
                                ->getStyle('B'.$l)
                                ->getBorders()
                                ->getOutline()
                                ->setBorderStyle(Border::BORDER_THIN)
                                ->setColor(new Color('000000'));

                        $sheet->getStyle('B'.$l)
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        $l++;
                    }
                }
                
            }
       
        }


        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);

        $writer = new Xlsx($spreadsheet);

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=sap.xlsx");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        $writer->save('php://output');

        echo "<script>window.close();</script>";

        //print_r($dtEscala);
        die;

    }

    private function printRes( $mes, $ano, $unid, $gestor )
    {
        ///// Busca os dados da escala \\\\\
        $data           = array();
        $daysMon        = array();
        $escalaTrab     = new EscalaTrabalho();
        $dtEscala       = $escalaTrab->getRestaurante($mes, $ano, $unid, $gestor);
    
        //// Caso não encontre a Escala \\\\
        if ( !$dtEscala )
        {
            $_SESSION['merr'] = "Ocorreu um erro na geração do relatório, tente novamente!";
            header("Location: " . BASE_URL . "relatorioEscala/");
            exit;
        }
        
        $nowY           = $ano;
        $month          = $mes;
        $allDays        = date("t", strtotime($nowY . "-" . $month));
        $ttPerTurno     = array();

        for($i = 1; $i <= $allDays; $i++)
        {
            $daysMon[$i] = $this->getLetter($i, $month, $nowY);
        }

        foreach( $dtEscala['escala'] AS $escala )
        {
            foreach( $escala->itensEscalas AS $itens )
            {
                for($i = 1; $i <= $allDays; $i++)
                {
                    if (isset($ttPerTurno[$itens['turnoID']]) && isset($ttPerTurno[$itens['turnoID']]['t'.$i]))
                    {
                        if($itens['t'.$i] == 0 || $itens['t'.$i] == 4)
                            $ttPerTurno[$itens['turnoID']]['t'.$i] = $ttPerTurno[$itens['turnoID']]['t'.$i] + 1;
                    } else {
                        if($itens['t'.$i] == 0 || $itens['t'.$i] == 4)
                            $ttPerTurno[$itens['turnoID']]['t'.$i] = 1;
                        else
                            $ttPerTurno[$itens['turnoID']]['t'.$i] = 0;
                    }
                }
            }
        }
    
        $data['ttPerTurno'] = $ttPerTurno;
        $data['infoMes']= $this->array[$month];
        $data['infoAno']= $nowY;
        $data['infoMesAno'] = $this->array[$month] . ' / ' . $nowY;
        $data['daysMon']    = $daysMon;

        $this->loadTemplateExterno('escalaTrabalho/escala/printRes', $data);
		exit();
    }

    private function generateFretamento($mes, $ano, $unid, $gestor)
    {

        $escalaTrab     = new EscalaTrabalho();
        $spreadsheet    = new Spreadsheet();
        $sheet          = $spreadsheet->getActiveSheet();

        /// Rodar 4 vezes para os 4 turnos definidos, gerando 1 arquivo por turno \\\
        $turnos  = array("1_TURNO_ESCALA", "2_TURNO_ESCALA", "3_TURNO_ESCALA", "ADM_ESCALA");
        $turnosDe= array("1º TURNO ESCALA", "2º TURNO ESCALA", "3º TURNO ESCALA", "ADM ESCALA");
        $hoursEn = array("06:00", "14:00", "22:00", "08:00");
        $hoursSa = array("14:00", "22:00", "06:00", "18:00");

        for($c=0; $c < 4; $c++)
        {
            $l = 1;

            // Add new sheet
            $objWorkSheet = $spreadsheet->createSheet($c); // Setting index when creating

            ############ MONTANDO O HEADER ###################
            ##################################################
                $objWorkSheet->setCellValue('A'.$l, 'EMPRESA CLIENTE');
                $objWorkSheet->setCellValue('B'.$l, 'ENDEREÇO EMP. CLIENTE');
                $objWorkSheet->setCellValue('C'.$l, 'TURNO');
                $objWorkSheet->setCellValue('D'.$l, 'CÓDIGO FUNCIONÁRIO');
                $objWorkSheet->setCellValue('E'.$l, 'NOME');
                $objWorkSheet->setCellValue('F'.$l, 'LONGITUDE');
                $objWorkSheet->setCellValue('G'.$l, 'LATITUDE');
                $objWorkSheet->setCellValue('H'.$l, 'UF');
                $objWorkSheet->setCellValue('I'.$l, 'HORÁRIO ENTRADA');
                $objWorkSheet->setCellValue('J'.$l, 'HORÁRIO SAIDA');
                $objWorkSheet->setCellValue('K'.$l, 'FREQUÊNCIA');
            ############### FIM O HEADER ######################
            ###################################################

            $dtEscala = $escalaTrab->getFretamentoData($mes, $ano, $unid, $gestor, ($c + 1));
          
            foreach($dtEscala as $esc)
            {
                $l++;
                ############ MONTANDO O BODY ###################
                ################################################
                    $objWorkSheet->setCellValue('A'.$l, 'EUROFARMA');
                    $objWorkSheet->setCellValue('B'.$l, "Rod. Pres. Castello Branco, 3565 - Itaqui, Itapevi - SP, 13308-700");
                    $objWorkSheet->setCellValue('C'.$l, $turnosDe[$c]);
                    $objWorkSheet->setCellValue('D'.$l, $esc->RE);
                    $objWorkSheet->setCellValue('E'.$l, $esc->NOME);
                    $objWorkSheet->setCellValue('F'.$l, $esc->LONG);
                    $objWorkSheet->setCellValue('G'.$l, $esc->LAT);
                    $objWorkSheet->setCellValue('H'.$l, $esc->UF);
                    $objWorkSheet->setCellValue('I'.$l, $hoursEn[$c]);
                    $objWorkSheet->setCellValue('J'.$l, $hoursSa[$c]);
                    $objWorkSheet->setCellValue('K'.$l, 'SABADO A DOMINGO');
                ############### FIM O HEADER ######################
                ###################################################
        
            }

            $objWorkSheet->getColumnDimension('A')->setWidth(20);
            $objWorkSheet->getColumnDimension('B')->setWidth(50);
            $objWorkSheet->getColumnDimension('C')->setWidth(20);
            $objWorkSheet->getColumnDimension('D')->setWidth(20);
            $objWorkSheet->getColumnDimension('E')->setWidth(35);
            $objWorkSheet->getColumnDimension('F')->setWidth(20);
            $objWorkSheet->getColumnDimension('G')->setWidth(20);
            $objWorkSheet->getColumnDimension('H')->setWidth(10);
            $objWorkSheet->getColumnDimension('I')->setWidth(20);
            $objWorkSheet->getColumnDimension('J')->setWidth(20);
            $objWorkSheet->getColumnDimension('K')->setWidth(20);

            // Rename sheet
            $objWorkSheet->setTitle("$turnos[$c]");
        }

        $spreadsheet->setActiveSheetIndex(0);

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=fretamento.xlsx");
        header("Cache-Control:max-age=0");
        header("Cache-Control:max-age=1");

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        echo "<script>window.close();</script>";

        die;
    }

}