<!DOCTYPE html>
<html lang="en">
<head>

	<!-- Basic Page Needs
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta charset="utf-8">
	<title></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Mobile Specific Metas
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSS
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<link rel="stylesheet" href="css/bulma.css">
	<link rel="stylesheet" href="css/custom.css">


	<style type="text/css">

		.center
		{
			display: flex;
			justify-content: center;
			align-items: center;
		}


	</style>


	<!-- Favicon
	–––––––––––––––––––––––––––––––––––––––––––––––––– -->
	<link rel="icon" type="image/png" href="images/favicon.png">

</head>
<body>



<section class="hero is-fullheight">

	<div class="hero-body">

		<div class="container">


			<div class="columns is-centered">



				<div class="column is-5-tablet is-4-desktop is-3-widescreen">

					<form method="post" action="index.php" name="loginform" class="box">


						<div class="container">
							<figure class="image center">
								<img src="images/gebwms_logo.png" style="max-width: 256px;">
							</figure>
						</div>


						<!--	Hack spacer...				-->
						<div style="height:24px;"></div>


						<div class="field">
						  <label for="" class="label">Username</label>
						  <div class="control">
							<input id="login_input_username" type="text" placeholder="e.g. mateusz" name="user_name" class="input" required>
						  </div>
						</div>

						<div class="field">
						  <label for="" class="label">Password</label>
						  <div class="control">
							<input id="login_input_password" type="password" placeholder="*******" name="user_password" class="input" required>
						  </div>
						</div>

						<div class="has-text-centered">
							<div class="field">
								<input type="submit" class="button gebwms_class is-centered"  name="login" value="Login" />
							</div>
						</div>

					</form>

				</div>

			</div>

		</div>

	</div>

</section>




</body>
</html>
