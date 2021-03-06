<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Template
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>
<!doctype html>
<html lang="<?php echo $language ?>">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo HTML::chars($page_title) ?><?php echo (!empty($page_title) ? ' | ' : '') . Kohana::config('site.site_name') ?></title>
	<link rel="icon" type="image/png" href="/ui/favicon.png" />
	<?php echo
		HTML::style('ui/boot.css'),
		HTML::style('ui/grid.css'),
		HTML::style('ui/typo.css'),
		HTML::style('ui/base.css');
	foreach ($skins as $skin_name => $available_skin) echo Less::style(
		$available_skin['path'],
		array(
			'title' => $skin_name,
			'rel'   => $skin_name == $skin ? 'stylesheet' : 'alternate stylesheet',
		),
		false,
		$skin_imports
	);
		//Less::style($skin, null, false, $skin_imports),
	echo
		HTML::style('ui/jquery-ui.css'),
		HTML::style('ui/site.css'),
		HTML::style('http://fonts.googleapis.com/css?family=Cantarell');
	?>

	<!--[if IE]><?php echo HTML::script('http://html5shiv.googlecode.com/svn/trunk/html5.js'); ?><![endif]-->
	<?php echo
		//HTML::script('http://www.google.com/jsapi?key=' . Kohana::config('site.google_api_key')),
		HTML::script('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'),
		HTML::script('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js'),
		HTML::script('http://maps.google.com/maps/api/js?sensor=false'),
		HTML::script('js/jquery.tools.min.js'); ?>

<?php echo Widget::get('head') ?>

</head>

<body id="<?php echo $page_id ?>" class="<?php echo $page_class ?>">

	<!-- BODY -->

	<div id="body" class="colmask leftmenu">
		<div class="colright">
			<div class="col1wrap">


				<!-- CONTENT -->

				<section id="main-wide" class="col1" role="main">

<?php echo Widget::get('ad_top') ?>

					<!-- MAIN -->

					<section id="<?php echo ($wide = Widget::get('wide')) ? 'wide' : 'main' ?>" class="unit <?php echo $wide ? 'size1of1' : 'size3of5' ?>">
						<header id="title">

<?php //echo Widget::get('breadcrumb') ?>

							<hgroup>
								<h2><?php echo $page_title ?></h2>
								<?php echo !empty($page_subtitle) ? '<span class="subtitle">' . $page_subtitle . '</span>' : '' ?>
							</hgroup>

<?php echo Widget::get('actions') ?>

						</header><!-- #title -->

<?php echo Widget::get('error') ?>
<?php echo $wide ? $wide : Widget::get('main') ?>

					</section><!-- <?php echo $wide ? 'wide' : 'main' ?> -->

<?php if ($wide && $main = Widget::get('main')): ?>
					<section id="main" class="unit size3of5">

<?php echo $main ?>

					</section><!-- main -->
<?php endif; ?>

					<!-- /MAIN -->

<?php if (!$wide || $main): ?>

					<!-- SIDE -->

					<aside id="side" class="unit size2of5" role="complementary">

<?php echo Widget::get('side') ?>

<?php echo Widget::get('ad_side') ?>

					</aside><!-- #side -->

					<!-- /SIDE -->

<?php endif; ?>

				</section><!-- #main-wide -->

				<!-- /CONTENT -->


			</div>


			<!-- SIDEBAR -->

			<section id="side-narrow" class="col2">

				<section id="logo" role="banner">
					<h1><?php echo HTML::anchor('/', Kohana::config('site.site_name')) ?></h1>
				</section>


<?php echo Widget::get('navigation') ?>

<?php echo Widget::get('sidebar') ?>

			</section>

			<!-- /SIDEBAR -->

		</div>
	</div>

	<!-- /BODY -->


	<!-- DOCK -->

	<section id="dock" class="pinned">
		<div class="content">

<?php echo Widget::get('dock') ?>

			<a id="customize" class="icon customize" onclick="$('#dock').toggleClass('open'); return false;"><?php echo __('Customize') ?></a>
		</div>
	</section><!-- #dock -->

	<!-- /DOCK -->


	<!-- FOOTER -->

	<footer id="footer">
		<div class="content" role="complementary">

<?php echo Widget::get('navigation') ?>
<?php echo Widget::get('footer') ?>

		</div>
		<div id="end" class="content" role="contentinfo">

<?php echo Widget::get('end') ?>

		</div>
	</footer><!-- #footer -->

	<!-- /FOOTER -->


	<div class="lightbox" id="slideshow">
		<div id="slideshow-images">
			<div class="items">
				<div>
					<div class="info"></div>
				</div>
			</div>
		</div>
		<a class="navi prev" title="<?= __('Previous') ?>">&laquo;</a>
		<a class="navi next" title="<?= __('Next') ?>">&raquo;</a>
		<a class="action close" title="<?= __('Close') ?>">&#10006;</a>
	</div>


<?php echo
	HTML::script('js/jquery.form.js'),
	HTML::script('js/jquery.text-overflow.js'),
	HTML::script('js/anqh.js'); ?>

<?php echo Widget::get('foot') ?>

</body>

</html>
