<?php

/**
 * modulo actions.
 *
 * @package    pusuario
 * @subpackage modulo
 * @author     maryitsv
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class moduloActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
  }
  

/**
  *Este metodo llama a la funcion pertinente dependiendo de la tarea que se envie
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
 public function executeCargar()
	{
		$task = '';
		$salida	='';
		$task = $this->getRequestParameter('task');

		switch($task){
			case "LISTINGMOD":
				$salida = $this->listarModulos();
				break;

			case "LISTINGGRAFICO":
				$salida = $this->listarModulosGrafico();
				break;

			case "UPDATEMOD":
				$salida = $this->updateModulo();
				break;

			case "CREATEMOD":
				$salida = $this->crearModulo();
				break;

			case "DELETEMOD":
				$salida = $this->eliminarModulo();
				break;
			default:
				$salida =  "({failure:true})";
				break;
		}

	
		return $this->renderText($salida);
	}

/**
  *Este metodo llama a la funcion para listar la jerarquia de modulos
  *@author maryit sanchez
  *@date 2010-06-30 
  **/  
        public function executeListarmodjer()
	{
		$salida	='';
		$salida = $this->listarModulosJerarquia(0);
	
		return $this->renderText($salida);
	}


/**
  *Este metodo permite la creacion de modulos
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
	protected function crearModulo()
	{
	
		$idProyecto =1;// $this->getRequestParameter('idProyecto');
		$padre=$this->getRequestParameter('modPadre');
                //validar dependencias en actualizacion
		$salida	='';
		
			  $modulo = new AgilhuModulo();			  
			  $modulo->setModNombre($this->getRequestParameter('modNombre'));
			  $modulo->setModEstado($this->getRequestParameter('modEstado'));
			  $modulo->setModDescripcion($this->getRequestParameter('modDescripcion'));
			  $modulo->setProId($this->getUser()->getAttribute('proyectoSeleccionado'));
			  $modulo->setModHabilitado($this->getRequestParameter('modHabilitado'));
			  if($padre != '')
			  {
				 $modulo->setModPadre($padre); 
			  }
                          else{
                               	 $modulo->setModPadre(NULL); 
			  
                          }
			  
			 
			 
			  try
	          {
	          	$modulo->save();
	          }
	          catch (Exception $excepcionModulo)
	          {
	             $salida = "({success: false, errors: { reason: 'Hubo un problema creando el modulo: ".$this->getRequestParameter('modNombre')."'}})";
	             return $salida;
	          }
			  $salida = "({success: true, mensaje:'El Modulo fue creado exitosamente'})";
		  
		  return $salida;
	}
/**
  *Este metodo permite la actualizacion de modulos
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
	protected function updateModulo()
	{
	
		$modId = $this->getRequestParameter('modId');
		$conexion = new Criteria();
		$conexion->add(AgilhuModuloPeer::MOD_ID, $modId);
		$modulo = AgilhuModuloPeer::doSelectOne($conexion);
		$salida = '';
                $padre=$this->getRequestParameter('modPadre');
                 //validar dependencias en actualizacion
		if($modulo)
		{
				$modulo->setModNombre($this->getRequestParameter('modNombre'));
				$modulo->setModDescripcion($this->getRequestParameter('modDescripcion')); 
				$modulo->setModEstado($this->getRequestParameter('modEstado'));
                                 if($padre != '')
				  {
					$modulo->setModPadre($padre); 
				  }
				  else{
					$modulo->setModPadre(NULL); 
				  
				  }
                              
				$modulo->setModHabilitado($this->getRequestParameter('modHabilitado'));
			 
			  	try
		      	{
		        	$modulo->save();
		      	}
		      	catch (Exception $excepcionModulo)
		      	{
		          	$salida = "({success: false, errors: { reason: 'Problemas al actualizar el modulo: ".$this->getRequestParameter('modNombre')."'}})";
		          	return $salida;
		        
		      	}
				$salida = "({success: true, mensaje:'El Modulo fue actualizado exitosamente'})";
		} else {
			$salida = "({success: false, errors: { reason: 'No se a actualizado el Modulo corecctamente'}})";
		}
	
		return $salida;
	}
/**
  *Este metodo permite eliminar modulos, siempre y cuando el modulo no tenga historias ni submodulos asociados
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
	protected function eliminarModulo()
	{
		$idmod = $this->getRequestParameter('idmodulo');

           //validamos que se pueda borrar
          	$conexionhijos = new Criteria();
		$conexionhijos->add(AgilhuModuloPeer::MOD_PADRE, $idmod);
                $hijos=AgilhuModuloPeer::doCount($conexionhijos);
             
          	$conexionhistorias = new Criteria();
		$conexionhistorias->add(AgilhuHistoriaUsuarioPeer::MOD_ID, $idmod);
                $historias=AgilhuHistoriaUsuarioPeer::doCount($conexionhistorias);


                if($hijos==0 && $historias==0){
		      $conexion = new Criteria();
		      $conexion->add(AgilhuModuloPeer::MOD_ID, $idmod);
		      
		      if($conexion)
		      {
			      $elemento = AgilhuModuloPeer::doSelectOne($conexion);
			      if($elemento){
				$elemento->delete();
			      
			      }
			      $salida = "({success: true, mensaje:'El Modulo fue eliminado exitosamente'})";
		      }
		      else
		      {
			      $salida = "({success: false, errors: { reason: 'No se pudo eliminar el Modulo'}})";
		      }
                }
                else{
                 $salida = "({success: false, errors: { reason: 'El Modulo no puede ser eliminado porque tiene submodulos o historias asociadas, verifique esto antes de eliminar el modulo'}})";


                }

		return $salida;
	}

/**
  *Este metodo retorna un listado de los modulos de un proyecto
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
        protected function listarModulos()
	{
         //      echo('a');
		/*Los modulo debe estar asociado a un proyecto*/
		$conexion = new Criteria();
		$conexion->add(AgilhuModuloPeer::PRO_ID,$this->getUser()->getAttribute('proyectoSeleccionado'));

		$numero_modulos = AgilhuModuloPeer::doCount($conexion);
	    //	$conexion->setLimit($this->getRequestParameter('limit'));
	//	$conexion->setOffset($this->getRequestParameter('start'));
		$modulos = AgilhuModuloPeer::doSelect($conexion);
		$pos = 0;
		$nbrows=0;
		$datos;
		
		foreach ($modulos As $temporal)
		{

			$datos[$pos]['modId']=$temporal->getModId();
			$datos[$pos]['modNombre']=$temporal->getModNombre();
			
		$pos++;
		$nbrows++;	
		}
		
		if($nbrows>0){
			$jsonresult = json_encode($datos);
			return '({"total":"'.$numero_modulos.'","results":'.$jsonresult.'})';
		}
		else {
			return '({"total":"0", "results":""})';
		}
	}

/**
  *Este metodo retorna nombre de un modulo
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
        protected function getNombreModulo($mod_id)
	{
		/*Los modulo debe estar asociado a un proyecto*/
                $nombreModulo='';
                if($mod_id!=null)
                {
	        $modulo = AgilhuModuloPeer::retrieveByPK($mod_id);
		      if($modulo){
		      $nombreModulo= $modulo->getModNombre();}
                }
                return $nombreModulo;	
	}
/**
  *Este metodo retorna una matriz con la información de los modulos, los padres y una breve descripcion, para generar un grafico organizacional
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
         protected function listarModulosGrafico()
	{
		/*Los modulo debe estar asociado a un proyecto*/
		$conexion = new Criteria();
		$conexion->add(AgilhuModuloPeer::PRO_ID,$this->getUser()->getAttribute('proyectoSeleccionado'));
		$modulos = AgilhuModuloPeer::doSelect($conexion);
		$pos = 0;
		$nbrows=0;
		$datos;

		
               /* $datos[$pos][0]='dan';
                $datos[$pos][1]='';
                $datos[$pos][2]='dante';
                $pos++;*/
		foreach ($modulos As $temporal)
		{
                        
			$datos[$pos][0]=$temporal->getModId().' - '.$temporal->getModNombre();

                       if($temporal->getModPadre()!=null || $temporal->getModPadre()!='')
                        {
			$datos[$pos][1]=$temporal->getModPadre().' - '.$this->getNombreModulo($temporal->getModPadre());
                        }
			else{
                        $datos[$pos][1]='';
                        }
			$datos[$pos][2]=$temporal->getModDescripcion();

			//$datos[$pos]=$temporal->getModDescripcion();
		$pos++;
		$nbrows++;	
		}
		if($nbrows>0){
			$jsonresult = json_encode($datos);
			//return '({"total":"'.$numero_modulos.'","results":'.$jsonresult.'})';
	  
		       $salida = '({success: true, data:'.$jsonresult.'})';
		}
		else
		{
			$salida = "({success: false, errors: { reason: 'No hay modulos o hubo prblemas con la base de datos'}})";
		}
         return $salida;
	}

/**
  *Este metodo retorna arbol en formato json que muestra la jerarquia de modulos y submodulos
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
	protected function listarModulosJerarquia($idModPadre)
	{
		/*Los modulo debe estar asociado a un proyecto*/

                $arbol="[";      
                $conexion = new Criteria();
		$conexion->add(AgilhuModuloPeer::PRO_ID,$this->getUser()->getAttribute('proyectoSeleccionado'));

		$numero_modulos = AgilhuModuloPeer::doCount($conexion);
	    	//$conexion->setLimit($this->getRequestParameter('limit'));
		//$conexion->setOffset($this->getRequestParameter('start'));
                if($idModPadre==0)
                {
		$conexion->add(AgilhuModuloPeer::MOD_PADRE,NULL);
                }

                else{
		    $conexion->add(AgilhuModuloPeer::MOD_PADRE,$idModPadre);
                }

		$modulos = AgilhuModuloPeer::doSelect($conexion);
		
		foreach ($modulos As $temporal)
		{

			$arbol.= "{";
		
			$arbol.=  "modId:'".$temporal->getModId()."'";
			$arbol.=  ",text:'".$temporal->getModNombre()."'";
			$arbol.=  ",id:'".$temporal->getModId()."'";

			$arbol.=  ",modNombre:'".$temporal->getModNombre()."'";
			$arbol.=  ",modDescripcion:'".$temporal->getModDescripcion()."'";
			$arbol.=  ",modEstado:'".$temporal->getModEstado()."'";
			$arbol.=  ",modFechaCreacion:'".$temporal->getCreatedAt()."'";
			$arbol.=  ",modPadre:'".$temporal->getModPadre()."'";
			$arbol.=  ",modHabilitado:'".$temporal->getModHabilitado()."'";
			$arbol.=  ",expanded: true"; 
			$arbol.=  ",iconCls:'task-folder'"; 

			if($this->esPadre($temporal->getModId()))
			{
			$arbol.=",children:";
			$arbol.=$this->listarModulosJerarquia($temporal->getModId());
			$arbol.="";

			}
			else{
			$arbol.=',leaf: true';     
			}
		      
			$arbol.='},';

		}
              
                $arbol.=']';
		return $arbol;
        }
/**
  *Este metodo nos indica si un modulo es o no padre de algun modulo de un proyecto particular
  *@author maryit sanchez
  *@date 2010-06-30 
  **/
	protected function esPadre($idMod)
	{
		/*Los modulo debe estar asociado a un proyecto*/
	  
                $conexion = new Criteria();
		$conexion->add(AgilhuModuloPeer::PRO_ID,$this->getUser()->getAttribute('proyectoSeleccionado'));
                $conexion->add(AgilhuModuloPeer::MOD_PADRE,$idMod);
		$numero_modulos = AgilhuModuloPeer::doCount($conexion);
		
		if($numero_modulos>0){return true;}
                else{ return false;}
		
	}



}
