<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';
if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function home(): never { header('Location: index.php'); exit; }
$errors = [];
$form = ['id'=>'','nombre'=>'','marca'=>'','unidades'=>'0','bodega_ubicacion'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) { http_response_code(400); exit('Solicitud no válida. Recarga la página.'); }
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) { $s=$pdo->prepare('DELETE FROM articulos WHERE id=?'); $s->execute([$id]); }
        home();
    }
    if ($action === 'save') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
        $units = trim((string)($_POST['unidades'] ?? ''));
        $form = ['id'=>$id?(string)$id:'','nombre'=>trim((string)($_POST['nombre']??'')),'marca'=>trim((string)($_POST['marca']??'')),'unidades'=>$units,'bodega_ubicacion'=>trim((string)($_POST['bodega_ubicacion']??''))];
        if ($form['nombre']==='' || mb_strlen($form['nombre'])>180) $errors[]='El nombre es obligatorio (máximo 180 caracteres).';
        if ($form['marca']==='' || mb_strlen($form['marca'])>120) $errors[]='La marca es obligatoria (máximo 120 caracteres).';
        if ($units==='' || !ctype_digit($units) || (float)$units>4294967295) $errors[]='Las unidades deben ser un entero igual o mayor que cero.';
        if ($form['bodega_ubicacion']==='' || mb_strlen($form['bodega_ubicacion'])>180) $errors[]='La bodega de ubicación es obligatoria (máximo 180 caracteres).';
        if (!$errors) {
            if ($id) { $s=$pdo->prepare('UPDATE articulos SET nombre=?,marca=?,unidades=?,bodega_ubicacion=? WHERE id=?'); $s->execute([$form['nombre'],$form['marca'],(int)$units,$form['bodega_ubicacion'],$id]); }
            else { $s=$pdo->prepare('INSERT INTO articulos(nombre,marca,unidades,bodega_ubicacion) VALUES(?,?,?,?)'); $s->execute([$form['nombre'],$form['marca'],(int)$units,$form['bodega_ubicacion']]); }
            home();
        }
    }
}
if (isset($_GET['edit']) && !$errors) {
    $id=filter_input(INPUT_GET,'edit',FILTER_VALIDATE_INT);
    if ($id) { $s=$pdo->prepare('SELECT id,nombre,marca,unidades,bodega_ubicacion FROM articulos WHERE id=?');$s->execute([$id]);$found=$s->fetch();if($found)$form=array_map(static fn($v)=>(string)$v,$found); }
}
$q=trim((string)($_GET['q']??''));
if($q!==''){$s=$pdo->prepare('SELECT * FROM articulos WHERE nombre LIKE ? OR marca LIKE ? OR bodega_ubicacion LIKE ? ORDER BY actualizado_en DESC,id DESC');$like='%'.$q.'%';$s->execute([$like,$like,$like]);$articulos=$s->fetchAll();}
else{$articulos=$pdo->query('SELECT * FROM articulos ORDER BY actualizado_en DESC,id DESC')->fetchAll();}
$total=(int)$pdo->query('SELECT COUNT(*) FROM articulos')->fetchColumn();
$units=(int)$pdo->query('SELECT COALESCE(SUM(unidades),0) FROM articulos')->fetchColumn();
$brands=(int)$pdo->query('SELECT COUNT(DISTINCT marca) FROM articulos')->fetchColumn();
$editing=$form['id']!=='';
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#172b4d"><title>InventarioPro · Control de inventario</title><link rel="stylesheet" href="styles.css"></head><body>
<aside class="sidebar"><a class="logo" href="index.php"><span class="logo-icon">▦</span><span>Inventario<span class="logo-accent">Pro</span><small>CONTROL DE EXISTENCIAS</small></span></a><div class="side-label">GESTIÓN</div><a class="nav-link active" href="index.php"><span class="nav-icon">▦</span> Inventario</a><div class="side-note"><span class="note-icon">✦</span><strong>Tu inventario, en orden</strong><p>Consulta existencias, marcas y bodegas desde un solo lugar.</p></div><div class="sidebar-bottom"><span class="status-dot"></span> Sistema de inventario<br><small>PHP + MySQL</small></div></aside>
<div class="page"><header class="topbar"><div><div class="breadcrumb">Panel principal <span>/</span> Inventario</div><h1>Control de inventario</h1><p class="subtitle">Administra tus artículos y mantén tus existencias al día.</p></div><div class="topbar-right"><div class="avatar">IP</div><div class="profile"><strong>Administrador</strong><span>Panel de control</span></div></div></header><main>
<section class="welcome-banner"><div class="welcome-copy"><span class="banner-kicker">CENTRO DE INVENTARIO</span><h2>Todo lo que tienes,<br>siempre bajo control.</h2><p>Registra artículos, controla unidades y ubica cada producto fácilmente.</p></div><div class="banner-art" aria-hidden="true"><div class="art-circle circle-one"></div><div class="art-circle circle-two"></div><div class="box-art"><div class="box-flap"></div><div class="box-tape"></div><div class="box-label"></div></div><div class="art-spark spark-one">✦</div><div class="art-spark spark-two">✧</div></div></section>
<section class="stats-grid"><article class="stat-card"><div class="stat-top"><span class="stat-icon blue">▣</span><span class="stat-caption">REFERENCIAS</span></div><div class="stat-value"><?= number_format($total,0,',','.') ?></div><div class="stat-foot">Artículos registrados</div></article><article class="stat-card"><div class="stat-top"><span class="stat-icon violet">▤</span><span class="stat-caption">EXISTENCIAS</span></div><div class="stat-value"><?= number_format($units,0,',','.') ?></div><div class="stat-foot">Unidades en inventario</div></article><article class="stat-card"><div class="stat-top"><span class="stat-icon green">◇</span><span class="stat-caption">MARCAS</span></div><div class="stat-value"><?= number_format($brands,0,',','.') ?></div><div class="stat-foot">Marcas diferentes registradas</div></article></section>
<section class="content-grid"><article class="card form-card"><div class="card-heading"><div class="heading-icon">＋</div><div><span class="section-kicker"><?= $editing?'MODIFICAR INFORMACIÓN':'AÑADIR INFORMACIÓN' ?></span><h2><?= $editing?'Editar artículo':'Registrar artículo' ?></h2></div></div>
<?php if($errors):?><div class="alert"><?php foreach($errors as $error):?><p><?=h($error)?></p><?php endforeach;?></div><?php endif;?>
<form method="post" action="index.php" class="inventory-form"><input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=h($form['id'])?>">
<label for="nombre">Nombre del artículo <span>*</span></label><div class="input-wrap"><span class="field-icon">▣</span><input id="nombre" name="nombre" maxlength="180" required placeholder="Ej. Teclado inalámbrico" value="<?=h($form['nombre'])?>"></div>
<label for="marca">Marca <span>*</span></label><div class="input-wrap"><span class="field-icon">◇</span><input id="marca" name="marca" maxlength="120" required placeholder="Ej. Logitech" value="<?=h($form['marca'])?>"></div>
<label for="unidades">Unidades disponibles <span>*</span></label><div class="input-wrap"><span class="field-icon">▤</span><input id="unidades" name="unidades" type="number" min="0" step="1" required placeholder="0" value="<?=h($form['unidades'])?>"></div>
<label for="bodega_ubicacion">Bodega de ubicación <span>*</span></label><div class="input-wrap"><span class="field-icon">⌖</span><input id="bodega_ubicacion" name="bodega_ubicacion" maxlength="180" required placeholder="Ej. Bodega A · Estante 03" value="<?=h($form['bodega_ubicacion'])?>"></div>
<div class="form-actions"><span class="required-note"><span>*</span> Campos obligatorios</span><div class="button-group"><?php if($editing):?><a class="btn-light" href="index.php">Cancelar</a><?php endif;?><button class="btn-primary" type="submit"><?= $editing?'✓ Guardar cambios':'＋ Crear artículo' ?></button></div></div></form></article>
<article class="card table-card"><div class="table-header"><div><span class="section-kicker">CATÁLOGO GENERAL</span><h2>Artículos registrados <span class="count-pill"><?=count($articulos)?></span></h2><p>Consulta, busca y actualiza los productos almacenados.</p></div></div>
<form class="search-bar" method="get" action="index.php"><span class="search-icon">⌕</span><input type="search" name="q" value="<?=h($q)?>" placeholder="Buscar nombre, marca o bodega..."><button type="submit">Buscar</button><?php if($q!==''):?><a href="index.php" class="clear-search" title="Limpiar búsqueda">×</a><?php endif;?></form>
<?php if(!$articulos):?><div class="empty-state"><div class="empty-illustration">▦</div><h3><?=$q!==''?'No hay resultados':'Tu inventario está esperando'?></h3><p><?=$q!==''?'Prueba con otro nombre, marca o ubicación.':'Registra tu primer artículo usando el formulario.'?></p></div>
<?php else:?><div class="table-wrap"><table><thead><tr><th>ARTÍCULO</th><th>MARCA</th><th>UNIDADES</th><th>UBICACIÓN / BODEGA</th><th>ACCIONES</th></tr></thead><tbody><?php foreach($articulos as $a):?><tr><td><div class="item-cell"><span class="item-avatar"><?=h(mb_strtoupper(mb_substr($a['nombre'],0,1)))?></span><div><strong><?=h($a['nombre'])?></strong><small>ID #<?=(int)$a['id']?></small></div></div></td><td><span class="brand-pill"><?=h($a['marca'])?></span></td><td><span class="units-pill <?=(int)$a['unidades']===0?'zero':''?>"><?=number_format((int)$a['unidades'],0,',','.')?></span></td><td><span class="location-text">⌖ <?=h($a['bodega_ubicacion'])?></span></td><td><div class="row-actions"><a href="index.php?edit=<?=(int)$a['id']?>" class="icon-action edit-action" title="Editar artículo" aria-label="Editar artículo">✎</a><form method="post" action="index.php" onsubmit="return confirm('¿Deseas eliminar este artículo? Esta acción no se puede deshacer.');"><input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$a['id']?>"><button class="icon-action delete-action" type="submit" title="Eliminar artículo" aria-label="Eliminar artículo">×</button></form></div></td></tr><?php endforeach;?></tbody></table></div><?php endif;?>
<div class="table-footer"><span>Mostrando <strong><?=count($articulos)?></strong> de <strong><?=$total?></strong> artículos</span><span class="footer-secure">▣ Datos almacenados en MySQL</span></div></article></section>
<footer class="page-footer"><span>© <?=date('Y')?> InventarioPro · Control de existencias</span><span>Un inventario claro, una gestión eficiente <b>✦</b></span></footer></main></div></body></html>
