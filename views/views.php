<!doctype html>
<html>

	<head>
		<meta charset="utf-8" />
		<link href="css/livre.css" rel="stylesheet" />
		<title>Billet simple pour l'Alaska</title>
	</head>
	
	<body>
		<header>
			<h1>Billet simple pour l'Alaska</h1>
		</header>
		<?php
		foreach($articles as $article): ?>
		<article>
			<h2><?php echo $article->getTitle() ?></h2>
			<p><?php echo $article->getContent() ?></p>
		</article>
		<?php endforeach ?>
		<footer class="footer">
			<b><i>"Billet simple pour l'Alaska"</i></b> ecrit par <a href="auteur.html"> Jean Forteroch </a>
		</footer>
	</body>

</html>