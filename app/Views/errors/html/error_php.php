<?php
// CI4: Plus besoin de BASEPATH check
?>

<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<h4>A PHP Error was encountered</h4>

<p>Severity: <?php echo $severity; ?></p>
<p>Message:  <?php echo $message; ?></p>
<p>Filename: <?php echo $filepath; ?></p>
<p>Line Number: <?php echo $line; ?></p>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>

	<p>Backtrace:</p>
	<?php foreach (debug_backtrace() as $error): ?>

		<?php if (isset($error['file']) && (!defined('SYSTEMPATH') || strpos($error['file'], realpath(SYSTEMPATH)) !== 0)): ?>

			<p style="margin-left:10px">
			File: <?php echo isset($error['file']) ? $error['file'] : 'N/A'; ?><br />
			Line: <?php echo isset($error['line']) ? $error['line'] : 'N/A'; ?><br />
			Function: <?php echo isset($error['function']) ? $error['function'] : 'N/A'; ?>
			</p>

		<?php endif ?>

	<?php endforeach ?>

<?php endif ?>

</div>