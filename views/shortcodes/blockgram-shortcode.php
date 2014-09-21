
<?php if ((isset($this->data['cookieValue']))||(isset($this->data['isFollower']))) { ?>
		
		<p><?php echo $this->data['content'];?></p>
		
<?php } else { ?>
	
	<div style="display: none;"><?php echo $this->data['content'];?></div>
	<div class="blockgram-container-hidden-content">
		<p style="font-size:1.2em; font-weight:bold;"><?php echo $this->data['message-block-content'];?></p>
		<a href="<?php echo $this->data['authInstagramURL'] ?>" class="blockgram-follow-button"><?php echo __('Follow to', 'blockgram').' '.$this->data['profileInfo']['fullName']; ?></a>
		<p class="blockgram-credits">Powered by <em><a href="http://www.todoInstagram.com" target="_blank" rel="nofollow">TodoInstagram.com</a></em></p>
	</div>
			
<?php } ?>