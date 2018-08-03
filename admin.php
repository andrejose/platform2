<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Sao_Paulo');

$db = new PDO('sqlite:storage/db.db');

session_start();

// Select the configuration date
$sql = "SELECT * FROM configuration";
$stmt = $db->prepare($sql);
$stmt->execute();
$config = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($config[0])) {
	unset($_SESSION['password']);
	header('location:index.php');
} else {
	$config = $config[0];
}

// Check if request type is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Check if action is set
	if (isset($_POST['action'])) {

		switch ($_POST['action']) {
			case 'update':

				// UPDATE CONFIGURATION
				$sql = "UPDATE configuration SET
					title = :title,
					intro = :intro,
					answered = :answered,
					access = :access,
					back = :back,
					stop = :stop
					WHERE id = '1'";
	    	    $stmt = $db->prepare($sql);
	    	    $stmt->bindValue(":title", $_POST['title'], PDO::PARAM_STR);
	    	    $stmt->bindValue(":intro", $_POST['intro'], PDO::PARAM_STR);
	    	    $stmt->bindValue(":answered", $_POST['answered'], PDO::PARAM_STR);
	    	    $stmt->bindValue(":access", $_POST['access'], PDO::PARAM_STR);
	    	    $stmt->bindValue(":back", $_POST['back'], PDO::PARAM_STR);
	    	    $stmt->bindValue(":stop", $_POST['stop'], PDO::PARAM_STR);
	    	    $stmt->execute();
				$template = 'configuration';

				$sql = "SELECT * FROM configuration";
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$config = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

				break;

			case 'createPassword':

				if ($_POST['password']) {
					$sql = "UPDATE configuration SET password = :password WHERE id = '1'";
		    	    $stmt = $db->prepare($sql);
		    	    $stmt->bindValue(":password", md5($_POST['password']), PDO::PARAM_STR);
		    	    $stmt->execute();
				}
				header('location:admin.php');

				break;

			case 'checkPassword':

				if (isset($_POST['password']) && md5($_POST['password']) == $config['password']) {
					$_SESSION['password'] = $config['password'];
					$template = 'configuration';
				} else {
					$message = 'Dados incorretos';
					$template = 'login';
				}
				break;

			case 'createSession':

				if (isset($_POST['hour']) && isset($_POST['minute']) ) {
					$sql = "INSERT INTO session (timetablehour, timetableminute) VALUES (:hour, :minute)";
		    	    $stmt = $db->prepare($sql);
		    	    $stmt->bindValue(":hour", $_POST['hour'], PDO::PARAM_STR);
		    	    $stmt->bindValue(":minute", $_POST['minute'], PDO::PARAM_STR);
		    	    if ($stmt->execute())
						$return = array('result' => 'success', 'message' => 'Data saved.', 'id' => $db->lastInsertId());
					else
						$return = array('result' => 'error', 'message' => 'Error on insert data.');
				} else {
					$return = array('result' => 'error', 'message' => 'Hour and minute not sent.');
				}

				echo json_encode($return);
				exit;

				break;

			case 'updateSession':

				if (isset($_POST['field']) && isset($_POST['value']) && isset($_POST['id']) ) {

				    $sql = "UPDATE session SET
							timetable{$_POST['field']} = :{$_POST['field']}
							WHERE id = :id";
		    	    $stmt = $db->prepare($sql);
		    	    $stmt->bindValue(":{$_POST['field']}", $_POST['value'], PDO::PARAM_STR);
		    	    $stmt->bindValue(":id", $_POST['id'], PDO::PARAM_STR);

		    	    if ($stmt->execute())
						$return = array('result' => 'success', 'message' => 'Data saved.', 'id' => $db->lastInsertId());
					else
						$return = array('result' => 'error', 'message' => 'Error on update data.');
				} else {
					$return = array('result' => 'error', 'message' => 'Hour and minute not sent.');
				}

				echo json_encode($return);
				exit;

				break;

			case 'deleteSession':

				if (isset($_POST['id'])) {

					$sql = "DELETE FROM questionnaire WHERE session_id = :id";
		    	    $stmt = $db->prepare($sql);
		    	    $stmt->bindValue(":id", $_POST['id'], PDO::PARAM_STR);
		    	    $stmt->execute();

					$sql = "DELETE FROM session WHERE id = :id";
		    	    $stmt = $db->prepare($sql);
		    	    $stmt->bindValue(":id", $_POST['id'], PDO::PARAM_STR);
		    	    $stmt->execute();

					$return = array('result' => 'success', 'message' => 'Data removed.', 'id' => $db->lastInsertId());

				} else {
					$return = array('result' => 'error', 'message' => 'Hour and minute not sent.');
				}

				echo json_encode($return);
				exit;

				break;

			case 'createQuestionnaire':

				// Select the biggest sort from questionnaire
				$sql = "SELECT * FROM questionnaire WHERE session_id = :session_id ORDER BY sort DESC LIMIT 1";
				$stmt = $db->prepare($sql);
	    	    $stmt->bindValue(":session_id", $_POST['session_id'], PDO::PARAM_STR);
				$stmt->execute();
				$questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$sort = (isset($questionnaires[0]['sort'])) ? $questionnaires[0]['sort'] + 1 : 1;

				$sql = "INSERT INTO questionnaire ('session_id', 'sort') VALUES (:session_id, :sort)";
	    	    $stmt = $db->prepare($sql);
	    	    $stmt->bindValue(":session_id", $_POST['session_id'], PDO::PARAM_STR);
	    	    $stmt->bindValue(":sort", $sort, PDO::PARAM_STR);
	    	    if ($stmt->execute())
					$return = array('result' => 'success', 'message' => 'Data saved.', 'id' => $db->lastInsertId(), 'sort' => $sort);
				else
					$return = array('result' => 'error', 'message' => 'Error on insert data.');

				echo json_encode($return);
				exit;

				break;

			case 'updateQuestionnaire':

				if (isset($_POST['field']) && isset($_POST['value']) && isset($_POST['id']) ) {

				    $sql = "UPDATE questionnaire SET
							{$_POST['field']} = :{$_POST['field']}
							WHERE id = :id";
		    	    $stmt = $db->prepare($sql);
		    	    $stmt->bindValue(":{$_POST['field']}", $_POST['value'], PDO::PARAM_STR);
		    	    $stmt->bindValue(":id", $_POST['id'], PDO::PARAM_STR);

		    	    if ($stmt->execute())
						$return = array('result' => 'success', 'message' => 'Data saved.', 'id' => $db->lastInsertId());
					else
						$return = array('result' => 'error', 'message' => 'Error on update data.');
				} else {
					$return = array('result' => 'error', 'message' => 'Hour and minute not sent.');
				}

				echo json_encode($return);
				exit;

				break;

			case 'deleteQuestionnaire':

				if (isset($_POST['id'])) {

					$sql = "DELETE FROM questionnaire WHERE id = :id";
		    	    $stmt = $db->prepare($sql);
		    	    $stmt->bindValue(":id", $_POST['id'], PDO::PARAM_STR);
		    	    $stmt->execute();

					$return = array('result' => 'success', 'message' => 'Data removed.', 'id' => $db->lastInsertId());

				} else {
					$return = array('result' => 'error', 'message' => 'Hour and minute not sent.');
				}

				echo json_encode($return);
				exit;

				break;

			default:
				header('location:admin.php');
				break;
		}
	} else {
		header('location:admin.php');
	}

} else {

	// Check if someone is logged
	if (isset($_SESSION['password'])) {
		// Define the template
		$template = 'configuration';
	} else {
		// Check if password is saved on database
		$template = ($config['password']) ? 'login' : 'createPassword';
	}
}

if ($template == 'configuration') {

	// Select sessions
	$sql = "SELECT * FROM session ORDER BY timetablehour, timetableminute";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Select questionnaires
	foreach($sessions as $key => $value) {
		$sql = "SELECT * FROM questionnaire WHERE session_id = {$value['id']} ORDER BY 'sort', id";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$sessions[$key]['questionnaires'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

}

?>
<!doctype html>
<html lang="pt">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title><?php echo $config['title'] ?></title>
		<link rel="shortcut icon" href="assets/img/favicon.ico">

		<!-- CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="assets/css/sb-admin.css">
		<link rel="stylesheet" href="assets/js/summernote/summernote-bs4.css">

		<!-- JS -->
        <script src="assets/js/jquery-2.1.1.min.js"></script>
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/summernote/summernote-bs4.js"></script>
        <script src="assets/js/admin.js"></script>

	</head>
	<body class="bg-dark">

		<div class="container-fluid">
			<h1 class="text-center mx-auto mt-5 text-light"><?php echo $config['title'] ?></h1>

			<?php if (isset($template) && $template == 'login') : ?>
			<div class="card card-login mx-auto mt-5">
				<div class="card-header">Autenticação</div>
				<div class="card-body">
					<form action="admin.php" method="post">
						<?php if (isset($message)) : ?>
						<div class="alert alert-danger"><?php echo $message ?></div>
						<?php endif; ?>
						<input type="hidden" name="action" value="checkPassword">
						<div class="form-group">
							<div class="form-label-group">
								<input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
								<label for="inputPassword">Senha</label>
							</div>
						</div>
						<button class="btn btn-primary btn-block">Login</button>
					</form>
					<div class="text-center">
						<a class="d-block small mt-3" href="index.php" target="_blank">Acessar a área de formulários</a>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if (isset($template) && $template == 'createPassword') : ?>
			<div class="card card-login mx-auto mt-5">
				<div class="card-header">Nova senha</div>
				<div class="card-body">
					<p class="text-center">Parece que a senha mestra ainda não está cadastrada. Digite uma senha para começar a utilizar o sistema.</p>
					<form action="admin.php" method="post">
						<input type="hidden" name="action" value="createPassword">
						<div class="form-group">
							<div class="form-label-group">
								<input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
								<label for="inputPassword">Senha</label>
							</div>
						</div>
						<button class="btn btn-primary btn-block">Criar senha</button>
					</form>
					<div class="text-center">
						<a class="d-block small mt-3" href="index.php" target="_blank">Acessar a área de formulários</a>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if (isset($template) && $template == 'configuration') : ?>

			<form action="admin.php" method="post">
				<input type="hidden" name="action" value="update">

				<div class="card mx-auto mt-5">
					<div class="card-header">Configuração</div>
					<div class="card-body">
						<div class="row">
							<div class="col-sm">
								<div class="form-group">
									<div class="form-label-group">
										<input type="text" name="title" id="inputTitle" class="form-control" placeholder="Título" required value="<?php echo $config['title'] ?>">
										<label for="inputTitle">Título</label>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm">
  								<textarea id="summernote" name="intro"><?php echo $config['intro'] ?></textarea>
  								<br>
  							</div>
						</div>
						<h6>Traduções</h6>
						<div class="row">
							<div class="col-sm">
								<div class="form-group">
									<div class="form-label-group">
										<input type="text" name="answered" id="inputAnswered" class="form-control" placeholder="Já respondi" required value="<?php echo $config['answered'] ?>">
										<label for="inputAnswered">Já respondi</label>
									</div>
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<div class="form-label-group">
										<input type="text" name="access" id="inputAccess" class="form-control" placeholder="Acessar formulário" required value="<?php echo $config['access'] ?>">
										<label for="inputAccess">Acessar questionário</label>
									</div>
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<div class="form-label-group">
										<input type="text" name="back" id="inputBack" class="form-control" placeholder="Voltar ao questionário" required value="<?php echo $config['back'] ?>">
										<label for="inputBack">Voltar ao questionário</label>
									</div>
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<div class="form-label-group">
										<input type="text" name="stop" id="inputStop" class="form-control" placeholder="Parar alarme" required value="<?php echo $config['stop'] ?>">
										<label for="inputStop">Parar alarme</label>
									</div>
								</div>
							</div>
						</div>
						<button class="btn btn-primary float-right">Salvar</button>
					</div>
				</div>
			</form>

			<main id="main">

				<?php foreach($sessions as $session) : ?>

				<div class="card mx-auto mt-5 session" data-id="<?php echo $session['id'] ?>">
					<div class="card-header"><span class="hour"><?php echo sprintf('%02d', $session['timetablehour']) . ':' . sprintf('%02d', $session['timetableminute']) ?></span> <button class="btn btn-sm btn-danger float-right btn-remove-session">Excluir</button></div>
					<div class="card-body">
						<div class="row">
							<div class="col-sm">
								<div class="form-group">
									<label>Hora</label>
									<select name="hour" class="form-control">
										<?php for($i = 0; $i < 24; $i++) : ?>
										<option value="<?php echo $i ?>" <?php if ($i == $session['timetablehour']) echo 'selected' ?>><?php echo $i ?></option>
										<?php endfor; ?>
									</select>
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<label>Minuto</label>
									<select name="minute" class="form-control">
										<?php for($i = 0; $i < 59; $i++) : ?>
										<option value="<?php echo $i ?>" <?php if ($i == $session['timetableminute']) echo 'selected' ?>><?php echo $i ?></option>
										<?php endfor; ?>
									</select>
								</div>
							</div>
						</div>

						<?php foreach($session['questionnaires'] as $questionnaire) : ?>
							<div class="row questionnaire" data-id="<?php echo $questionnaire['id'] ?>">
								<hr>
								<div class="col-sm-3">
									<div class="form-group">
										<div class="form-label-group">
											<input type="text" name="title" id="qTitle<?php echo $questionnaire['id'] ?>" class="form-control" placeholder="Título" required value="<?php echo $questionnaire['title'] ?>">
											<label for="qTitle<?php echo $questionnaire['id'] ?>">Título</label>
										</div>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<div class="form-label-group">
											<input type="text" name="link" id="qLink<?php echo $questionnaire['id'] ?>" class="form-control" placeholder="Link" required value="<?php echo $questionnaire['link'] ?>">
											<label for="qLink<?php echo $questionnaire['id'] ?>">Link</label>
										</div>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<div class="form-label-group">
											<input type="text" name="delay" id="qDelay<?php echo $questionnaire['id'] ?>" class="form-control" placeholder="Delay" required value="<?php echo $questionnaire['delay'] ?>">
											<label for="qDelay<?php echo $questionnaire['id'] ?>">Delay</label>
										</div>
									</div>
								</div>
								<div class="col-sm-2">
									<div class="form-group">
										<div class="form-label-group">
											<input type="number" name="sort" id="qSort<?php echo $questionnaire['id'] ?>" class="form-control" placeholder="Ordem" required value="<?php echo $questionnaire['sort'] ?>">
											<label for="qSort<?php echo $questionnaire['id'] ?>">Ordem</label>
										</div>
									</div>
								</div>
								<div class="col-sm-1">
									<button class="btn btn-sm btn-block btn-danger float-right btn-remove-questionnaire">Excluir</button>
								</div>
							</div>

						<?php endforeach; ?>

						<div class="text-center btn-add-questionnaire-container">
							<hr>
							<a href="javascript:;" class="btn btn-primary btn-sm btn-add-questionnaire">Adicionar questionário</a>
						</div>
					</div>
				</div>

				<?php endforeach; ?>

			</main>

			<div class="text-center">

				<button class="btn btn-sm btn-primary mt-5 btn-add-session">Adicionar sessão</button>

				<hr>

				<div class="text-light mb-5">
					<a class="small mt-3 text-light btn-clear-cache" href="index.php" target="_blank">Limpar cache</a> |
					<a class="small mt-3 text-light" href="index.php" target="_blank">Acessar a área de formulários</a> |
					<a class="small mt-3 text-light" href="logout.php">Sair</a>
				</div>
			</div>
			<?php endif; ?>

			<div id="session-model" style="display:none">
				<div class="card mx-auto mt-5 session">
					<div class="card-header"><span class="hour">10:30</span> <button class="btn btn-sm btn-danger float-right btn-remove-session">Excluir</button></div>
					<div class="card-body">
						<div class="row">
							<div class="col-sm">
								<div class="form-group">
									<label>Hora</label>
									<select name="hour" class="form-control">
										<?php for($i = 0; $i < 24; $i++) : ?>
										<option value="<?php echo $i ?>"><?php echo $i ?></option>
										<?php endfor; ?>
									</select>
								</div>
							</div>
							<div class="col-sm">
								<div class="form-group">
									<label>Minuto</label>
									<select name="minute" class="form-control">
										<?php for($i = 0; $i < 59; $i++) : ?>
										<option value="<?php echo $i ?>"><?php echo $i ?></option>
										<?php endfor; ?>
									</select>
								</div>
							</div>
						</div>

						<div class="text-center btn-add-questionnaire-container">
							<hr>
							<a href="javascript:;" class="btn btn-primary btn-sm btn-add-questionnaire">Adicionar questionário</a>
						</div>
					</div>
				</div>
			</div>

			<div id="questionnaire-model" style="display:none">

				<div class="row questionnaire">

					<hr>

					<div class="col-sm-3">
						<div class="form-group">
							<div class="form-label-group">
								<input type="text" name="title" class="form-control" placeholder="Título">
								<label>Título</label>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<div class="form-label-group">
								<input type="text" name="link" class="form-control" placeholder="Link">
								<label>Link</label>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<div class="form-label-group">
								<input type="text" name="delay" class="form-control" placeholder="Delay">
								<label>Delay</label>
							</div>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							<div class="form-label-group">
								<input type="number" name="sort" class="form-control" placeholder="Ordem">
								<label>Ordem</label>
							</div>
						</div>
					</div>
					<div class="col-sm-1">
						<button class="btn btn-sm btn-block btn-danger float-right btn-remove-questionnaire">Excluir</button>
					</div>
				</div>
			</div>

		</div>
	</body>
</html>