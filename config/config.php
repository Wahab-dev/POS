<?php
const DATABASE_HOST = '127.0.0.1';
const DATABASE_USER_NAME = 'root';
const DATABASE_USER_PASSWORD = '';
const DATABASE_INSTANCE_NAME = "pos_new";
const USER_SESSION_TIMEOUT_MINUTES = 30;

/*define log config level
 * -----------------------------------------
 NONE    ...		No levels of log printed
 ALL     ...		All below type of logs printed
 FATAL	Highest	Very severe error events that will presumably lead the application to abort.
 ERROR	...		Error events that might still allow the application to continue running.
 WARN	...		Potentially harmful situations which still allow the application to continue running.
 INFO	...		Informational messages that highlight the progress of the application at coarse-grained level.
 DEBUG	...		Fine-grained informational events that are most useful to debug an application.
 TRACE	Lowest	Finest-grained informational events.
 */
const PRINTED_LOG_LEVEL = "DEBUG";

const DEFAULT_LOCALE = "en";
