<?php
include "../include/session.php";
if (!loginCheck()) {
	return;
}
logout();
?>