<?php

namespace controller;

/**
 * controller - Base controller for all other controllers.
 */
abstract class Controller {
	
	/**
	 *
	 * @var declared as protected
	 */
	protected $_controller;
	protected $_action;
	protected $_facade;
	protected $_facadeBaseName;
	protected $errorLog;
	                               
	// A constructor method with $viewFolderName, $action, $facadeBaseName as Parameters
	public function __construct($facadeBaseName) {
		$this->_controller = ucwords ( __CLASS__ );
		$this->_facadeBaseName = $facadeBaseName;
		if (isset ( $facadeBaseName )) {
			$facadeClassName = $facadeBaseName . 'Facade';
			$facadePathName = '\facade\\' .$facadeClassName;
			$this->_facade = new $facadePathName ();
		}
		//SessionHelper::_check_status ();
	}
	
	// Facade method to create new facade class files
	protected function _createFacade($facadeBaseName) {
		$facadeName = $facadeBaseName . 'Facade';
		$facadePathName = '\facade\\' .$facadeName;
		return new $facadePathName ();
	}
	
	// Instantiate error logger
	protected function _errorLogger($className) {
		$this->errorLog = new ErrorLogger ( $className );
	}
	
	// Method to check is Error Level Is enabled.
	protected function _isErrorEnabled() {
		return $this->errorLog->isErrorEnabled ();
	}
	
	// Method to check is Trace Level Is enabled.
	protected function _isTraceEnabled() {
		return $this->errorLog->isTraceEnabled ();
	}
	
	// Method to check is Fatal Level Is enabled.
	protected function _isFatalEnabled() {
		return $this->errorLog->isFatalEnabled ();
	}
	
	// Method to check is warn Level Is enabled.
	protected function _isWarnEnabled() {
		return $this->errorLog->isWarnEnabled ();
	}
	
	// Method to check is Debug Level Is enabled.
	protected function _isDebugEnabled() {
		return $this->errorLog->isDebugEnabled ();
	}
	
	// Method to check is Error Level Is enabled.
	protected function _isInfoEnabled() {
		return $this->errorLog->isInfoEnabled ();
	}
	
	// Method to catch Error
	protected function _error($message, $throwable = NULL) {
		return $this->errorLog->error ( $message, $throwable );
	}
	
	// Method to catch Trace
	protected function _trace($message, $throwable = NULL) {
		return $this->errorLog->trace ( $message, $throwable );
	}
	
	// Method to catch Fatal Error
	protected function _fatal($message, $throwable = NULL) {
		return $this->errorLog->fatal ( $message, $throwable );
	}
	
	// Method to catch Warning
	protected function _warn($message, $throwable = NULL) {
		return $this->errorLog->warn ( $message, $throwable );
	}
	
	// Method to catch Debug
	protected function _debug($message, $throwable = NULL) {
		return $this->errorLog->debug ( $message, $throwable );
	}
	
	// Method to catch Info
	protected function _info($message, $throwable = NULL) {
		return $this->errorLog->info ( $message, $throwable );
	}
	
	// Get the variable Dump
	protected function _getVarDump($var) {
		return $this->errorLog->get_var_dump ( $var );
	}
}