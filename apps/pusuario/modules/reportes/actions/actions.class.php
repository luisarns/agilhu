<?php

/**
 * reportes actions.
 *
 * @package    agilhu
 * @subpackage reportes
 * @author     maryit
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class reportesActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
//$pro_id=$this->getUser()->getAttribute('proyectoSeleccionado');
 
  //$this->reporteProyecto($pro_id,1,true,true,true);

  }

/*controla la eticion dependiendo de lo que se pide se responde
*@autor maryit sanchez
*@date mayo -6-2010
*/
  public function executeCargar(){
		$task = '';
		$salida	='';
		$task = $this->getRequestParameter('task');
		$pro_id=$this->getUser()->getAttribute('proyectoSeleccionado');
		
		switch($task){
			case "LISTARMOD":
				$salida = $this->listarModulos($pro_id);
				break;

                        case "LISTARHISTORIAS":
				$salida = $this->listarHuXPro();
				break;

			default://$this->reporteProyecto($pro_id,1,true,true,true);
				//$salida =  "({failure:true})";
				break;
		}
	return $this->renderText($salida);	
  }

/**
  *Este metodo retorna la ultima vesrion de las hu y se caracteriza por que entrega unos tiempos de trabajo
  *@author maryit sanchez
  *@date 2010-03-25 
  **/
protected function listarHuXPro()
  {
    $unidad_configurada=$this->getRequestParameter('escalaTiempo');
    $eqDia=$this->getRequestParameter('equivalenteDia');
    $eqMes=$this->getRequestParameter('equivalenteMes');
/*
equivalenteDia	8
equivalenteHora	1
equivalenteMes	40
escalaTiempo	Hora*/

    $pro_id = $this->getUser()->getAttribute('proyectoSeleccionado');
    $cv = new Criteria();
    $cv->clearSelectColumns();
    $cv->add(AgilhuHistoriaUsuarioPeer::PRO_ID, $pro_id);
    $cv->addSelectColumn(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA);
    $cv->addSelectColumn('MAX('.AgilhuHistoriaUsuarioPeer::HIS_VERSION.') AS HIS_VERSION');
    $cv->addGroupByColumn(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA);

    $historias_ultimamente_actualizadas = AgilhuHistoriaUsuarioPeer::doSelectStmt($cv);
    $datos;
    $fila_version=0;
    $fila=0;
    while ($historia = $historias_ultimamente_actualizadas->fetch(PDO::FETCH_NUM)) {

    $ch = new Criteria();
    $ch->add(AgilhuHistoriaUsuarioPeer::PRO_ID, $pro_id);
    $ch->add(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA,$historia[0]);
    $ch->add(AgilhuHistoriaUsuarioPeer::HIS_VERSION,$historia[1]);
    $historias = AgilhuHistoriaUsuarioPeer::doSelect($ch);

	foreach($historias As $historia)
	    {
	      $datos[$fila]['his_id'] = $historia->getHisId();
	      $datos[$fila]['mod_id'] = $historia->getModId();
	    //  $datos[$fila]['mod_nombre'] = $this->getModNombre($historia->getModId());
              $datos[$fila]['mod_nombre'] = $this->getModNombreRuta($historia->getModId());

	      $datos[$fila]['his_identificador_historia'] = $historia->getHisIdentificadorHistoria();
	      $datos[$fila]['his_nombre'] = $historia->getHisNombre();
	      $datos[$fila]['created_at'] = $historia->getCreatedAt(); 
	      $datos[$fila]['his_creador'] = $this->getUsuUsuario($historia->getHisCreador());
	      $datos[$fila]['updated_at'] = $historia->getUpdatedAt();
	      $datos[$fila]['his_prioridad'] = $historia->getHisPrioridad();
	      $datos[$fila]['his_responsable'] = $historia->getHisResponsable();
	      $datos[$fila]['pro_id'] = $historia->getProId();
	      $datos[$fila]['his_dependencias'] = $historia->getHisDependencias();
	      $datos[$fila]['his_riesgo'] = $historia->getHisRiesgo();
	      $datos[$fila]['his_descripcion'] = $historia->getHisDescripcion();
	      $datos[$fila]['his_version'] = $historia->getHisVersion();
              $datos[$fila]['his_tiempo_estimado']=$this->convertirUnidad($historia->getHisUnidadTiempo(),$unidad_configurada,$eqDia,$eqMes,$historia->getHisTiempoEstimado());
              $datos[$fila]['his_tiempo_real']=$this->convertirUnidad($historia->getHisUnidadTiempo(),$unidad_configurada,$eqDia,$eqMes,$historia->getHisTiempoReal());

	      $datos[$fila]['his_unidad_tiempo'] = $unidad_configurada;//$historia->getHisUnidadTiempo() ;
	      $datos[$fila]['his_tipo_actividad'] = $historia->getHisTipoActividad();
	      $datos[$fila]['his_observaciones'] = $historia->getHisObservaciones();
	      $datos[$fila]['his_actor'] = $historia->getHisActor(); //cambio v1.1
	      $datos[$fila]['his_iteracion'] = $historia->getHisIteracion(); //cambio v1.1

	      $fila++;
	    }
   } 
    $salida = '({"total":"'.$fila++.'","hispro":'.json_encode($datos).'})';
    return $salida;
  }


/**
  *Este metodo retorna el equivalente en hora, mes o dia dado una configuracion actual
  *@author maryit sanchez
  *@date 2010-04-20 
  **/
 protected function convertirUnidad($unidad_historia,$unidad_configurada,$eqDia,$eqMes,$tiempoAc)
  {

 $tiempoEq=0.0;//tiempoEquivalente;
    if($unidad_historia=='Hora'){
      
      if($unidad_configurada=='Hora')
      {$tiempoEq=$tiempoAc;}
      
      if($unidad_configurada=='Dia')
      {$tiempoEq=$tiempoAc/$eqDia;}
      
      if($unidad_configurada=='Mes')
      {$tiempoEq=$tiempoAc/($eqDia*$eqMes);}

    }
    if($unidad_historia=='Dia'){
      
      if($unidad_configurada=='Hora')
      {$tiempoEq=$tiempoAc*$eqDia;}
      
      if($unidad_configurada=='Dia')
      {$tiempoEq=$tiempoAc;}
      
      if($unidad_configurada=='Mes')
      {$tiempoEq=$tiempoAc/$eqMes;}

    }

    if($unidad_historia=='Mes'){
      
      if($unidad_configurada=='Hora')
      {$tiempoEq=$tiempoAc*$eqDia*$eqMes;}
      
      if($unidad_configurada=='Dia')
      {$tiempoEq=$tiempoAc*$eqMes;}
      
      if($unidad_configurada=='Mes')
      {$tiempoEq=$tiempoAc;}

    }

    return  $tiempoEq;
  }

/**
  *Este metodo retorna la ruta de nombres de un modulo, esta es construida recorriendo los padre del modulo
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
 protected function getModNombreRuta($mod_id)
  {
  $ruta='';
    try{
             $modulo=AgilhuModuloPeer::retrieveByPK($mod_id);

	    if($modulo)
	    {
               if($modulo->getModPadre()==null || $modulo->getModPadre()=='')
               {
               $ruta=$modulo->getModNombre();
	       return $ruta;
               }

               else{
               $ruta=$this->getModNombreRuta($modulo->getModPadre());
               $ruta.='->'.$modulo->getModNombre();
               return $ruta;
               }
	    }
	    else{
	     return  '';
	    }
    }catch(Exception $e){
    return  '';
    }
  }

/**
  *Este metodo retorna nombre de un modulo
  *@author maryit sanchez
  *@date 2010-03-25 
  **/
 protected function getModNombre($mod_id)
  {
    try{
	    $conexion = new Criteria();
	    $conexion->add(AgilhuModuloPeer::MOD_ID, $mod_id);
	    
	    $agilhu_modulo= AgilhuModuloPeer::doSelectOne($conexion);

	    if($agilhu_modulo)
	    {
	     return $agilhu_modulo->getModNombre();
	    }
	    else{
	     return  'desconocido';
	    }
    }catch(Exception $e){
    return  'desconocido';
    }

  }


/**
  *Este metodo retorna el login de un usuario
  *@author maryit sanchez
  *@date 2010-03-25 
  **/
 protected function getUsuUsuario($usu_id)//cambio
  {
   try{
    $conexion = new Criteria();
    $conexion->add(AgilhuUsuarioPeer::USU_ID, $usu_id);
    $agilhu_usuario= AgilhuUsuarioPeer::doSelectOne($conexion);

	    if($agilhu_usuario)
	    {
	     return $agilhu_usuario->getUsuUsuario();
	    }
	    else{
	     return  'desconocido';
	    }
   }
   catch(Exception $e){
    return  'desconocido';
   }
  }

/***********************************************************Generar documentos*********************/
/*llama a el documento
*@autor maryit sanchez
*@date mayo -6-2010
*/
  public function executeDocumento(){
         $task = '';
         $salida='';
         $task = $this->getRequestParameter('task');
         $pro_id=$this->getUser()->getAttribute('proyectoSeleccionado');
         $mod_ids = $this->getRequestParameter('mod_ids');
         $info_pro = $this->getRequestParameter('info_pro');
         $info_mod = $this->getRequestParameter('info_mod');
         $info_eval = $this->getRequestParameter('info_eval');
                
         if($task == 'DetalleHistorias'){	
         $this->reporteProyecto($pro_id,$mod_ids,$info_pro,$info_mod,$info_eval);
         }
         if($task == 'listadoHistorias'){ 
         $this->reporteListadoHistoriasProyecto($pro_id,'modulos');
         }
  }

/********************************************************Genera documento con info de las historias***********/
  
/*retorna una lista con los modulos del proyecto
*@autor maryit sanchez
*@date mayo -6-2010
*/
  protected function listarModulos($pro_id)
	{
		/*Los modulo debe estar asociado a un proyecto*/
		$conexion = new Criteria();
		$conexion->add(AgilhuModuloPeer::PRO_ID,$pro_id);
		$cantidad_modulos = AgilhuModuloPeer::doCount($conexion);
		$modulos = AgilhuModuloPeer::doSelect($conexion);
		$pos = 0;
		$datos;
		
		foreach ($modulos As $modulo)
		{
			$datos[$pos]['mod_id']=$modulo->getModId();
			$datos[$pos]['mod_nombre']=$modulo->getModNombre();
			
		$pos++;
		}
		
		if($pos>0){
			$jsonresult = json_encode($datos);
			return '({"total":"'.$cantidad_modulos.'","results":'.$jsonresult.'})';
		}
		else {
			return '({"total":"0", "results":""})';
		}
	}


/*Crea un pdf
*@autor maryit sanchez
*@date mayo -7-2010
*/
  //si se incluyen o no alguna informacion, controla dependiendo de la congiguracion
  public function reporteProyecto($pro_id,$mod_ids,$incl_pro,$incl_mod,$incl_eval)
  {
        require_once("dompdf/dompdf_config.inc.php");
        $html=stripslashes('<html>');

        $html='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="es-ES">';


        $html.=stripslashes('<head> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> ');

        $html.=stripslashes('
	<style>
	body {font-size: 1.1em; margin: 38px 40px 38px 38px;}
	h1 {font-size: 1.7em;color: red;text-align: center;}
        h2 {font-size: 1.4em;color: blue;text-align: left;}
        h3 {font-size: 1.3em;color: black;text-align: left;}
	table {width: 100%; border-collapse: collapse;border:1px solid #000;}
	td {}
        .general {font-family: arial,san-serif;text-align: justify; }
        .descripcion {font-size: 1em;padding: 10px;border:1px solid #000; }
	</style>
	');

        //  text-align: justify; 
        //border:1px solid #000; margin ar aba der izq
  $fecha = time (); 
  $fechaMostrar=date ( "d/m/Y h:i:s", $fecha ); 

  $htmlscript='<script type="text/php">';
  $htmlscript .='$font = Font_Metrics::get_font("verdana");';
  $htmlscript .='$size = 9;';
  $htmlscript .='$color = array(0,0,0);';
  $htmlscript .='$text_height = Font_Metrics::get_font_height($font, $size);';
  $htmlscript .='$foot = $pdf->open_object();';
  $htmlscript .='$w = $pdf->get_width();';
  $htmlscript .='$h = $pdf->get_height();';
  $htmlscript .='$y = $h - $text_height - 30;';
  $htmlscript .='$pdf->close_object();';
  $htmlscript .='$pdf->add_object($foot, "all");';
  $htmlscript .='$text = " Fecha '.$fechaMostrar.'  ,  P&aacute;gina {PAGE_NUM} de {PAGE_COUNT}";';  

  // Center the text
  $htmlscript .='$width = Font_Metrics::get_text_width("fehca 01/01/2001 09:12:12  ,  pagina 1 de 2", $font, $size);';
  $htmlscript .='$pdf->page_text($w / 2 - $width / 2, $y, $text, $font, $size, $color);';

  $htmlscript .='</script>';

        $html.=stripslashes("</head><body>$htmlscript<div class='general'>");//$htmlscript
        $htmlDatosProyecto=$this->incluirDatosProyecto($pro_id,$incl_pro);
        $mod_ids=2;//cambiar aqui
	$html.=	$htmlDatosProyecto;
		  
        $htmlDatosModulos=$this->incluirDatosModulo($pro_id,$mod_ids,$incl_mod);
        $html.=	$htmlDatosModulos;
	
        $html.=stripslashes('</div></body></html>');
       
/*
        $html=str_replace('á' , '&aacute;' , $html);
        $html=str_replace('é' , '&eacute;' , $html);
        $html=str_replace('í' , '&iacute;' , $html);
        $html=str_replace('ó' , '&oacute;' , $html);
        $html=str_replace('ú' , '&uacute;' , $html);*/
 //echo($html);
       

        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper('letter','portrait');
        $dompdf->render();
        $dompdf->stream("reporteProyecto.pdf");

        exit(0);

   
  }

/*Incluye los datos basicos de un proyecto en un pdf
*@autor maryit sanchez
*@date mayo -6-2010
*/
  public function incluirDatosProyecto($pro_id,$incl_pro){
    //traigo a el proyecto    
    $proyecto = AgilhuProyectoPeer::retrieveByPK($pro_id);
    $html ='<h1>'; 
    $html .= $proyecto->getProNombre().'<br/>';
    $html .= $proyecto->getProNombreCorto().'<br/>';
    $html .='</h1><br/>'; 


    if($incl_pro=='true')
   {	
    $text ="<b>Información detallada del proyecto</b>";
    $text .="<br/><b>Nombre proyecto</b>: ".$proyecto->getProNombre();
    $text .="<br/><b>Nombre corto proyecto</b>: ".$proyecto->getProNombreCorto();
    $text .="<br/><b>Area aplicacion proyecto</b>: ".$proyecto->getProAreaAplicacion();
    $text .="<br/><b>Descripci&oacute;n proyecto:</b><br/>".$proyecto->getProDescripcion();
    $html .=$text;
   }

    return $html;
  }

/*Incluye los datos basicos los modulos indicados por el usuario en un pdf
*@autor maryit sanchez
*@date mayo -6-2010
*/
  public function incluirDatosModulo($pro_id,$mod_ids,$incl_mod){
  $html ='';
   $conexion=new Criteria();
    $conexion->add(AgilhuModuloPeer::PRO_ID,$pro_id);
    $modulos = AgilhuModuloPeer::doSelect($conexion);
    $i=0;
	foreach($modulos as $modulo){
		//traigo a el proyecto    
 //if($i==0){
             $html .='<h2>MODULO: '.$modulo->getModNombre().'</h2><br/>';
       
            if($incl_mod=='true')
            {
            $text ="<b>Nombre modulo</b>: ".$modulo->getModNombre();
            $text .="<br/><b>Estado modulo</b>: ".$modulo->getModEstado();
            $text .="<br/><b>Descripci&oacute;n modulo:</b><br/>".$modulo->getModDescripcion();
            $html .=$text;
            $htmlDatosHis=$this->incluirDatosHis($pro_id,$modulo->getModId(),false);
	    $html .=$htmlDatosHis;
            //llamado a his
            }
	    else{
	    //lamado a his
            $htmlDatosHis=$this->incluirDatosHis($pro_id,$modulo->getModId(),false);
	    $html .=$htmlDatosHis;
	    }
//$i++;
//}
		//aqui se debe llamar a las hu
		//y debe haber un if para ver si incluimos la informacion del modulo
	}
	
    return $html;
  }  


 /*Incluye los datos basicos de una historia de usuario en un pdf
*@autor maryit sanchez
*@date mayo -7-2010
*/
  //impirme todas las hu de un modulos espcifico,puede escojer si desea mostrar la informacion de la evaluacion
  public function incluirDatosHis($pro_id,$mod_id,$incl_info_eval,$incl_html=false){
    $html='';
    $cv = new Criteria();
    $cv->clearSelectColumns();
    $cv->add(AgilhuHistoriaUsuarioPeer::PRO_ID, $pro_id);	
    $cv->addSelectColumn(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA);
  //  $cv->add(AgilhuHistoriaUsuarioPeer::MOD_ID,$mod_id);//no se si poner esto, no finalmete es mala idea porque repite las historias por modulo
    $cv->addSelectColumn('MAX('.AgilhuHistoriaUsuarioPeer::HIS_VERSION.') AS HIS_VERSION');
    $cv->addGroupByColumn(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA);
    $cv->addAscendingOrderByColumn(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA);
		
    $historias_ultimas_versiones = AgilhuHistoriaUsuarioPeer::doSelectStmt($cv);
    
    while ($historia = $historias_ultimas_versiones->fetch(PDO::FETCH_NUM)) {

		$ch = new Criteria();
		$ch->add(AgilhuHistoriaUsuarioPeer::PRO_ID, $pro_id);
                $ch->add(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA,$historia[0]);
		$ch->add(AgilhuHistoriaUsuarioPeer::MOD_ID,$mod_id);
		$ch->add(AgilhuHistoriaUsuarioPeer::HIS_VERSION,$historia[1]);
                
		$historias = AgilhuHistoriaUsuarioPeer::doSelect($ch);

		foreach($historias As $historia)
			{
                        $html .='<br/><br/>';
                        $html .='<h3>Historia '.$historia->getHisIdentificadorHistoria().'-'.$historia->gethisNombre().'</h3>';
                        $html .='<br/>';
			$tablehtml="<table >";

				$tablehtml.="<tr>"; 
					$tablehtml.="<td>";
						$tablehtml.="<b>Identificador</b>: ".$historia->getHisIdentificadorHistoria();		
					$tablehtml.="</td>";
					$tablehtml.="<td>";
						$tablehtml.="<b>Nombre</b>: ".$historia->getHisNombre();		
					$tablehtml.="</td>";
					$tablehtml.="<td>";
						$tablehtml.="<b>Prioridad</b>: ".$historia->getHisPrioridad();		
					$tablehtml.="</td>";
				$tablehtml.="</tr>";

				$tablehtml.="<tr>"; 
					$tablehtml.="<td>";
						$tablehtml.="<b>Dependencias</b>: ".$historia->getHisDependencias();		
					$tablehtml.="</td>";
					$tablehtml.="<td>";
						$tablehtml.="<b>Responsables</b>: ".$historia->getHisResponsable();		
					$tablehtml.="</td>";
					$tablehtml.="<td>";
						$tablehtml.="<b>Riesgo</b>: ".$historia->getHisRiesgo();		
					$tablehtml.="</td>";
				$tablehtml.="</tr>";
				
				$tablehtml.="<tr>"; 
					$tablehtml.="<td>";
						$tablehtml.="<b>Unidad de tiempo</b>: ".$historia->getHisUnidadTiempo();		
					$tablehtml.="</td>";
					$tablehtml.="<td>";
						$tablehtml.="<b>Tiempo Estimado</b>: ".$historia->getHisTiempoEstimado();		
					$tablehtml.="</td>";
					$tablehtml.="<td>";
						$tablehtml.="<b>Tiempo Real</b>: ".$historia->getHisTiempoReal();		
					$tablehtml.="</td>";
				$tablehtml.="</tr>";
			
				
				$tablehtml.="<tr>"; 
					$tablehtml.="<td>";
						$tablehtml.="<b>Creada</b>: ".substr($historia->getCreatedAt(),0,10);//sacar los primeros 10 caracteres		
					$tablehtml.="</td>";
					$tablehtml.="<td>";
						$tablehtml.="<b>Actualizada</b>: ".substr($historia->getUpdatedAt(),0,10);		
					$tablehtml.="</td>";
					$tablehtml.="<td>";
						$tablehtml.="<b>Autor</b>: ".$this->getUsuUsuario($historia->getHisCreador());//cambiar		
					$tablehtml.="</td>";
				$tablehtml.="</tr>";
			
			$tablehtml.="</table>";
		        $html .= $tablehtml;
			  

                        $descipcionhu="<div class=\"descripcion\" >";//style=\"border: 1px coral solid;width: 510px;\" 
			$descipcionhu.="<b>Descripci&oacute;n</b><br/>";	
                        $desc2=$historia->getHisDescripcion();           
			$descipcionhu.="".$desc2."<br/>";	

			$descipcionhu.="<b>Observaciones</b>: ".$historia->getHisObservaciones();		
			$descipcionhu.="</div>";	
                        $html .= $descipcionhu;
			  //agregar el if de la evaluacion
			}
   } 
    return $html;   
  }

/*************************************************Genera la hoja de control del poryecto**********/



/*Crea un pdf
*@autor maryit sanchez
*@date junio -21-2010
*/
  //si se incluyen o no alguna informacion, controla dependiendo de la congiguracion
  public function reporteListadoHistoriasProyecto($pro_id,$criterio_orden)
  {
        require_once("dompdf/dompdf_config.inc.php");
        $html=stripslashes('<html>');

        $html='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="es-ES">';
        $html.=stripslashes('<head> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> ');

        $html.=stripslashes('
	<style>
	body {font-size: 1.0em; margin: 45px;}
	h1 {font-size: 1.4em;color: red;text-align: center;}
        .modulo {font-size: 1.1em;color: blue;text-align: left;font-weight:bold}
        h3 {font-size: 1.1em;color: black;text-align: left;}
	table {width: 100%;border-collapse: collapse;}
	td, th {border:1px solid #000;}
        thead {background-color: #eeeeee;}
        .general {font-family: arial,san-serif;}
        .descripcion {font-size: 1em;padding: 10px;border:1px solid #000; }
	</style>
	');
/*
thead {
  background-color: #eeeeee;
}
*/
  
        $html.=stripslashes("</head><body><div class='general'>");//$htmlscript
        $htmlDatosProyecto=$this->incluirNombreProyecto($pro_id);
       
	$html.=	$htmlDatosProyecto;

        $html.="<table>
                <thead> <tr>
                <th>Id</th> <th>Nombre</th> <th>Prioridad</th> <th>Dependencias</th> <th>Responsable</th> 
                <th>Iteracion</th> <th>Tiempo</th>  <th>Estimado</th> <th>Real</th> 
                </tr> </thead>";
        $htmlDatosModulos=$this->incluirListadoOrden($pro_id,$criterio_orden);

        $html.=	$htmlDatosModulos;
        $html.=	"</table>";
	
        $html.=stripslashes('</div></body></html>');
        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper('letter','landscape');
        $dompdf->render();
        $dompdf->stream("reporteResumidoProyecto.pdf");

        exit(0);
  }


/*Incluye los datos basicos de un proyecto en un pdf
*@autor maryit sanchez
*@date junio -21-2010
*/
  public function incluirNombreProyecto($pro_id){
    //traigo a el proyecto    
    $proyecto = AgilhuProyectoPeer::retrieveByPK($pro_id);
    $html ='<h1>'; 
    $html .= $proyecto->getProNombre().'<br/>';
    $html .= $proyecto->getProNombreCorto();
    $html .='</h1>'; 

    return $html;
  }

/*Incluye el nombre del citerio por el cual esta ordenado el listado por defecto el nombre del modulo
*@autor maryit sanchez
*@date junio -21-2010
*/
  public function incluirListadoOrden($pro_id,$criterio_orden){
    $html ='';
    $conexion=new Criteria();
    if($criterio_orden=='modulos')
    {
        $conexion->add(AgilhuModuloPeer::PRO_ID,$pro_id);
        $modulos = AgilhuModuloPeer::doSelect($conexion);
	foreach($modulos as $modulo){

                
            $html .='<tr><td colspan="9" class="modulo" >MODULO: '.$modulo->getModNombre().'</td></tr>';
	    //lamado a his
            $htmlDatosHis=$this->incluirListaHis($pro_id,'modulos',$modulo->getModId());
	    $html .=$htmlDatosHis;
	}
    }
	
    return $html;
  }  


 /*Incluye los datos basicos de una historia de usuario en un pdf
*@autor maryit sanchez
*@date junio -21-2010
*/
  //impirme todas las hu de un modulos espcifico,puede escojer si desea mostrar la informacion de la evaluacion
  public function incluirListaHis($pro_id,$criterio,$criterio_clave){
    $html='';
    $cv = new Criteria();
    $cv->clearSelectColumns();
    $cv->add(AgilhuHistoriaUsuarioPeer::PRO_ID, $pro_id);	
    $cv->addSelectColumn(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA);
    $cv->addSelectColumn('MAX('.AgilhuHistoriaUsuarioPeer::HIS_VERSION.') AS HIS_VERSION');
    $cv->addGroupByColumn(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA);
    $cv->addAscendingOrderByColumn(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA);
		
    $historias_ultimas_versiones = AgilhuHistoriaUsuarioPeer::doSelectStmt($cv);
    $tablehtml='';

    while ($historia = $historias_ultimas_versiones->fetch(PDO::FETCH_NUM)) {

		$ch = new Criteria();
		$ch->add(AgilhuHistoriaUsuarioPeer::PRO_ID, $pro_id);
                $ch->add(AgilhuHistoriaUsuarioPeer::HIS_IDENTIFICADOR_HISTORIA,$historia[0]);
		$ch->add(AgilhuHistoriaUsuarioPeer::MOD_ID,$criterio_clave);
		$ch->add(AgilhuHistoriaUsuarioPeer::HIS_VERSION,$historia[1]);
               
		$historias = AgilhuHistoriaUsuarioPeer::doSelect($ch);

		foreach($historias As $historia)

		{
			$tablehtml ="<tr>"; 
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisIdentificadorHistoria();		
				$tablehtml.="</td>";
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisNombre();		
				$tablehtml.="</td>";
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisPrioridad();		
				$tablehtml.="</td>";
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisDependencias();		
				$tablehtml.="</td>";
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisResponsable();		
				$tablehtml.="</td>";
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisRiesgo();		
				$tablehtml.="</td>";
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisUnidadTiempo();		
				$tablehtml.="</td>";
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisTiempoEstimado();		
				$tablehtml.="</td>";
				$tablehtml.="<td>";
					$tablehtml.=$historia->getHisTiempoReal();		
				$tablehtml.="</td>";
			$tablehtml.="</tr>";
		        $html.= $tablehtml;  
		}
   } 
    return $html;   
  }


}
