<div class="wrap">
	
	<?php if(isset($this->data['saved'])){?>
		<div class="updated"><p><?php _e($this->data['saved'], 'blockgram'); ?></p></div>
	<?php }?>
	
	<?php if(isset($this->data['errorMessage'])){?>
		<div class="error"><p><?php _e($this->data['error'], 'blockgram'); ?>: <b><?php echo $this->data['errorMessage']?></b></p></div>
	<?php }?>

	<?php if(isset($this->data['response_message'])){?>
		<div class="error"><p><?php _e($this->data['error'], 'blockgram'); ?>: <b><?php echo $this->data['response_message']?></b></p></div>
	<?php }?>
	
	<h2><?php _e('Blockgram setting pannel', 'blockgram'); ?></h2>
	
	<?php
			
	$bgramObject = new BlockgramPlugin();
	$bgramOptions = $bgramObject->getBgramOptions();
		
	?>
	<form method="post">
		<div class="blockgram-form-layout">
			<div class="blockgram-form-horizontal">
				
				<?php if(empty($this->data['dataClientID']) && empty($this->data['dataClientSecret'])){ ?>


				<p><span class="label-text-input"><?php _e('Client ID', 'blockgram'); ?></span><input type="text" size="50" value="" name="bgram-client-id" id="bgram-client-id" /></p>

				<p><span class="label-text-input"><?php _e('Client Secret', 'blockgram'); ?></span><input type="text" size="50" value="" name="bgram-client-secret" id="bgram-client-secret" /></p>

				<input type="submit" class="button-primary" value=<?php _e('Save settings', 'blockgram'); ?> />

				<?php }else if(empty($this->data['app_access_token'])){?>

				<h4><?php _e('These are your client data.', 'blockgram');?></h4>
				<ul>
					<li><?php _e('CLIENT ID: ', 'instagram'); echo $this->data['dataClientID']; ?></li>
					<li><?php _e('CLIENT SECRET: ', 'instagram'); echo $this->data['dataClientSecret']; ?></li>
				</ul>
				<a href="<?php echo $this->data['authInstagramURL']; ?>" class="button-primary"><?php _e('Instagram login', 'blockgram'); ?></a>
				<input type="submit" class="button" name="blockgram-reset-auth-settings" value="<?php _e('Reset settings', 'blockgram'); ?>" />

				<?php } else { ?>

				<?php if( $this->data['activation_message']){?>
				<div class="success">
					<?php echo __('Your application is authorized, have fun!', 'blockgram'); ?>
				</div>
				<?php } ?>

				<div class="bgram-container">

					<div class="profile-info">
						<?php if( isset( $this->data['profile_username'] ) ){ ?>
						<h3><?php echo $this->data['profile_username'];?></h3>
						<?php } ?>
						<div class="profile-picture">
							<?php if( isset( $this->data['profile_picture'] ) ){?>
							<img src="<?php echo $this->data['profile_picture'];?>" alt="<?php _e('Profile photo', 'blockgram'); ?>">
							<?php }?>
						</div>
						<div class="followers-count">
							<?php if( isset( $this->data['followers_count'] ) ){ ?>
							<p><?php echo __('You have got this followers since you installed Blockgram: ', 'blockgram').$this->data['followers_count'];?></p>
							<?php } ?>
						</div>
					</div>

					<div class="bgram-divider"></div>
					<label>
						<?php _e('Message to unlock hidden content', 'blockgram'); ?>
						<input type="text" size="50" value="<?php echo $this->data['message-block-content'];?>" name="blockgram-message-block-content" id="blockgram-message-block-content" />
					</label>
					<br>
					<label>
						<?php _e('I want to follow to TodoInstagram on Instagram', 'blockgram'); ?>
						<input type="checkbox" class="bgram-checkbox" name="blockgram-follow-todoinstagram" value="follow" <?php checked( $this->data['app_follow_todoinstagram'], 'follow' ); ?> />
					</label>
				</div>

				<div style="margin-top: 20px;">
					<input type="submit" class="button" name="blockgram-reset-auth-settings" value="<?php _e('Reset settings', 'blockgram'); ?>" />
					<input type="submit" class="button-primary" name="blockgram-update-settings" value="<?php _e('Save settings', 'blockgram'); ?>" />
				</div>
				<?php } ?>
			</div>
		</div>
	</form>
</div>