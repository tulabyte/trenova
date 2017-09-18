<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_dbconn = "localhost";
$database_dbconn = "tulaborm_trenova";
$username_dbconn = "tulaborm_trenova";
$password_dbconn = "TRsql_1620";
$dbconn = mysql_pconnect($hostname_dbconn, $username_dbconn, $password_dbconn) or trigger_error(mysql_error(),E_USER_ERROR);

mysql_select_db($database_dbconn, $dbconn);
?>