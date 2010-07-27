<?php
// auto-generated by sfViewConfigHandler
// date: 2010/07/02 11:43:47
$response = $this->context->getResponse();


  $templateName = sfConfig::get('symfony.view.'.$this->moduleName.'_'.$this->actionName.'_template', $this->actionName);
  $this->setTemplate($templateName.$this->viewName.$this->getExtension());



  if (!is_null($layout = sfConfig::get('symfony.view.'.$this->moduleName.'_'.$this->actionName.'_layout')))
  {
    $this->setDecoratorTemplate(false === $layout ? false : $layout.$this->getExtension());
  }
  else if (is_null($this->getDecoratorTemplate()) && !$this->context->getRequest()->isXmlHttpRequest())
  {
    $this->setDecoratorTemplate('' == 'layout' ? false : 'layout'.$this->getExtension());
  }
  $response->addHttpMeta('content-type', 'text/html', false);
  $response->addMeta('title', 'Gestion de usuarios', false, false);
  $response->addMeta('description', 'El administrador del sistema gestiona usuarios', false, false);
  $response->addMeta('keywords', 'usuarios, gestion', false, false);
  $response->addMeta('language', 'es', false, false);
  $response->addMeta('robots', 'index, follow', false, false);

  $response->addStylesheet('extjs/resources/css/ext-all.css', '', array ());
  $response->addStylesheet('extjs/resources/css/xtheme-blue.css', '', array ());
  $response->addStylesheet('main.css', '', array ());
  $response->addJavascript('extjs/adapter/ext/ext-base.js', '', array ());
  $response->addJavascript('extjs/ext-all-debug.js', '', array ());
  $response->addJavascript('extjs/src/locale/ext-lang-es.js', '', array ());
  $response->addJavascript('usuario/agGestionUsuario.js', '', array ());
  $response->addJavascript('aux/Ext.ux.grid.Search.js', '', array ());
  $response->addJavascript('logueo/login.js', '', array ());
  $response->addJavascript('aux/funciones.js', '', array ());


