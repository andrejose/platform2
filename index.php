<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Sao_Paulo');

$db = new PDO('sqlite:storage/db.db');

// Select the configuration date
$sql = "SELECT * FROM configuration";
$stmt = $db->prepare($sql);
$stmt->execute();
$config = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($config[0])) {
	$sql = "INSERT INTO configuration (id, title, intro, answered, access, back, stop) VALUES (1, '', '', '', '', '', '')";
    $stmt = $db->prepare($sql);
    $stmt->execute();

	$sql = "SELECT * FROM configuration";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$config = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
} else {
	$config = $config[0];
}



// Select sessions
$sql = "SELECT * FROM session ORDER BY timetablehour, timetableminute";
$stmt = $db->prepare($sql);
$stmt->execute();
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Select questionnaires
foreach($sessions as $key => $value) {
	$sql = "SELECT * FROM questionnaire WHERE session_id = {$value['id']} ORDER BY sort, id";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$sessions[$key]['questionnaires'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="pt-br" class="no-js">
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo $config['title'] ?></title>
		<link rel="shortcut icon" href="assets/img/favicon.ico">

        <!-- CSS -->
		<link rel="stylesheet" type="text/css" href="assets/css/default.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/component.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/button.css" />
	</head>
	<body>
		<div class="container">
			<header class="clearfix">
				<span id="today"></span>
                <h1><?php echo $config['title'] ?></h1>
			</header>
			<div class="main">
	            <?php if ($config['intro']) : ?>
				<div id="intro">
	            	<?php echo $config['intro'] ?>
		            <hr>
		        </div>
	        	<?php endif; ?>
				<?php if (!$config['title']) : ?>
				<p>Parece que o sistema ainda não foi configurado. Acesse a área administrativa pelo link:</p>
				<p><a href="admin.php" class="button">Área administrativa</a></p>
				<?php endif; ?>
				<ul class="cbp_tmtimeline">
					<?php foreach($sessions as $key => $value) : ?>
						<?php if ($value['questionnaires']) : ?>
						<li id="time<?php echo sprintf('%02d', $value['timetablehour']) . sprintf('%02d', $value['timetableminute']) ?>" class="session <?php if ($value['timetablehour']== 0 && $value['timetableminute'] == 0) echo 'notimedefined'; ?>">
							<?php if ($value['timetablehour']!= 0 && $value['timetableminute'] != 0): ?>
							<time class="cbp_tmtime" datetime="<?php echo sprintf('%02d', $value['timetablehour']) . ':' . sprintf('%02d', $value['timetableminute']) ?>"><span><?php echo sprintf('%02d', $value['timetablehour']) . ':' . sprintf('%02d', $value['timetableminute']) ?></span><span class="cronometer" date-hour="<?php echo $value['timetablehour'] ?>" date-minute="<?php echo $value['timetableminute'] ?>" ></span></time>
							<?php endif ?>
							<div class="cbp_tmicon cbp_tmicon-screen" id="session<?php echo $value['id'] ?>"></div>
							<div class="cbp_tmlabel">
							<?php foreach($value['questionnaires'] as $k => $v) : ?>
	                            <div class="questionnaire" id="q<?php echo $v['id'] ?>" data-delay="<?php echo $v['delay'] ?>">
									<h2><?php echo $v['title'] ?></h2>
									<?php if ($v['delay'] > 0) : ?>
									<p class="q-intro">É necessário responder o questionário anterior.</p>
									<p class="q-countdown-container">Tempo para responder: <span class="q-countdown"></span></p>
									<?php endif; ?>
	                                <a href="<?php echo $v['link'] ?>" target="_blank" class="button answer"><?php echo $config['access'] ?></a>
	                                <div class="answered">
	                                    <a href="javascript:;" class="button" data-id="q<?php echo $v['id'] ?>"><?php echo $config['answered'] ?></a>
	                                    <a href="<?php echo $v['link'] ?>" target="_blank" class="button_error"><?php echo $config['back'] ?></a>
	                                </div>
	                                <a href="javascript:;" class="button stop blink_me"><?php echo $config['stop'] ?></a>
	                                <div class="sound_container"></div>
	                            </div>
							<?php endforeach; ?>
							</div>
						</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

        <!-- JS -->
		<script src="assets/js/modernizr.custom.js"></script>
        <script src="assets/js/jquery-2.1.1.min.js"></script>

        <script src="assets/js/jquery.plugin.min.js"></script>
        <script src="assets/js/jquery.countdown.min.js"></script>
        <script src="assets/js/jquery.countdown-pt-BR.js"></script>

        <script src="assets/js/script.js"></script>
	</body>
</html>
