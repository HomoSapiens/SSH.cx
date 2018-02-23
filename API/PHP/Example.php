<?php
/**
 * Example script with all class actions
 * Last info in https://ssh.cx/api.html
 */

include_once 'SshcxApi.php';

$token = 'SECRET';
$short = '3r4G'; // Can be parsed by SshcxApi::getShortFromUrl('https://ssh.cx/3r4G')
$id = 123;

/**
 * Create new short url for google.com
 */
SshcxApi::getInstance($token)->postUrl('http://google.com');

/**
 * Get all user URLs
 */
SshcxApi::getInstance($token)->getUrls();

/**
 * Get info about url by id
 */
SshcxApi::getInstance($token)->getUrlById($id);

/*
 * Get info about url by short code
 */
SshcxApi::getInstance($token)->getUrlByShort($short);

/**
 * Delete url from ssh.cx by id
 */
SshcxApi::getInstance($token)->deleteUrlById($id);

/**
 * Delete url from ssh.cx by short code
 */
SshcxApi::getInstance($token)->deleteUrlByShort($short);

/**
 * Delete all user urls from ssh.cx
 */
SshcxApi::getInstance($token)->deleteAllUrls();

/**
 * Upload file filename.zip to ssh.cx
 */
SshcxApi::getInstance($token)->postFile('/path/to/file/filename.zip');

/**
 * Get info about uploaded files
 */
SshcxApi::getInstance($token)->getFiles();

/**
 * Get info about uploaded file by id
 */
SshcxApi::getInstance($token)->getFileById($id);

/**
 * Get info about uploaded file by short code
 */
SshcxApi::getInstance($token)->getFileByShort($short);

/**
 * Delete user file from ssh.cx by id
 */
SshcxApi::getInstance($token)->deleteFileById($id);

/**
 * Delete user file from ssh.cx by short code
 */
SshcxApi::getInstance($token)->deleteFileByShort($short);

/**
 * Delete all user files from ssh.cx
 */
SshcxApi::getInstance($token)->deleteAllFiles();