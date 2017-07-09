<?php
// Config sample. Should be renamed to config.php when finished.
// MySQL host, username, pass, DB
$act_mysql_host = null;
$act_mysql_user = null;
$act_mysql_pass = null;
$act_mysql_db = 'nnas';
// Server environment (one letter & 1 number, such as: 'S1')
$act_srv_env = 'D1';
// Server mode, 0 for dev and 1 for prod
$act_srv_mode = 0;
// Server minimum system versions for each platform, null for none
/*$act_srv_min_ver = array(
							0 => 2050,
							1 => 2040,
								);
*/
$act_srv_min_ver = null;

// OAuth2
// Access token expiry in seconds
$act_oauth_expiry = 3600;
