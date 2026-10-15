<?php

if (!defined('ADODB_DIR')) {
	die();
}

require_once(ADODB_DIR . '/drivers/adodb-mysql.inc.php');

class ADODB_mysqlt extends ADODB_mysql {
	var $databaseType = 'mysqlt';
	var $ansiOuter = true;
	var $hasTransactions = true;
	var $autoRollback = true;

	function __construct()
	{
		parent::__construct();
		global $ADODB_EXTENSION;
		if ($ADODB_EXTENSION) {
			$this->rsPrefix .= 'ext_';
		}
	}

	function SetTransactionMode($transaction_mode)
	{
		$this->_transmode = $transaction_mode;
		if (empty($transaction_mode)) {
			$this->Execute('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
			return;
		}
		if (!stristr($transaction_mode, 'isolation')) {
			$transaction_mode = 'ISOLATION LEVEL ' . $transaction_mode;
		}
		$this->Execute('SET SESSION TRANSACTION ' . $transaction_mode);
	}

	function BeginTrans()
	{
		if ($this->transOff) {
			return true;
		}
		$this->transCnt++;
		$this->Execute('SET AUTOCOMMIT=0');
		$this->Execute('BEGIN');
		return true;
	}

	function CommitTrans($ok = true)
	{
		if ($this->transOff) {
			return true;
		}
		if (!$ok) {
			return $this->RollbackTrans();
		}
		if ($this->transCnt) {
			$this->transCnt--;
		}
		$this->Execute('COMMIT');
		$this->Execute('SET AUTOCOMMIT=1');
		return true;
	}

	function RollbackTrans()
	{
		if ($this->transOff) {
			return true;
		}
		if ($this->transCnt) {
			$this->transCnt--;
		}
		$this->Execute('ROLLBACK');
		$this->Execute('SET AUTOCOMMIT=1');
		return true;
	}

	function RowLock($tables, $where = '', $flds = '1 as adodb_ignore')
	{
		if ($this->transCnt == 0) {
			$this->BeginTrans();
		}
		if ($where) {
			$where = ' where ' . $where;
		}
		$rs = $this->Execute("select $flds from $tables $where for update");
		return !empty($rs);
	}
}

class ADORecordSet_mysqlt extends ADORecordSet_mysqli {
	var $databaseType = 'mysqlt';
}

class ADORecordSet_ext_mysqlt extends ADORecordSet_mysqlt
{
}
