<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Comments
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php echo Form::open() ?>
<fieldset class="horizontal">
	<ul>
		<?php if (isset($private) && $private): ?>
		<?php echo Form::checkbox_wrap('private', '1', $values, array('onchange' => "\$('input[name=comment]').toggleClass('private', this.checked)\""), '<abbr class="private" title="' . __('Private comment') . '">' . __('Priv') . '</abbr>') ?>
		<?php endif; ?>

		<?php echo Form::input_wrap('comment', $values, array('maxlength' => 300), null, $errors) ?>

		<li><?php echo Form::submit(false, __('Comment')) ?></li>
	</ul>
	<?php echo Form::csrf() ?>
</fieldset>
<?php echo Form::close() ?>

<?php foreach ($comments as $comment):
	$classes = array();

	if ($comment->private) {
		$classes[] = 'private';
	}

	// Viewer's post
	if ($user && $comment->author->id == $user->id) {
		$classes[] = 'my';
		$mine = true;
	} else {
		$mine = false;
	}

	// Topic author's post
	if ($comment->author->id == $comment->user->id) {
		$classes[] = 'owner';
	}
 ?>

<article id="comment-<?php echo $comment->id ?>" class="<?php echo implode(' ', $classes) ?>">


	<header>
		<?php echo HTML::avatar($comment->author->avatar, $comment->author->username, true) ?>
		<?php echo HTML::user($comment->author, $comment->author->username) ?>
		<small class="ago"><?php echo HTML::time(Date::short_span($comment->created, true, true), $comment->created) ?></small>

		<?php if ($user && $comment->user->id == $user->id || $mine): ?>
		<span class="actions alt">
			<?php if ($private && !$comment->private): ?>
			<?php echo HTML::anchor(sprintf($private, $comment->id), __('Set as private'), array('class' => 'action small comment-private')) ?>
			<?php endif; ?>
			<?php echo HTML::anchor(sprintf($delete, $comment->id), __('Delete'), array('class' => 'action small comment-delete')) ?>
		</span>
		<?php endif; ?>

	</header>

	<p>
		<?php echo $comment->private ? '<abbr title="' . __('Private comment') . '">' . __('Priv') . '</abbr>: ' : '' ?>
		<?php echo Text::smileys(Text::auto_link_urls(HTML::chars($comment->comment))) ?>
	</p>
</article>

<?php endforeach; ?>
