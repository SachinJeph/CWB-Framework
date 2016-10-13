<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Required meta tags always come first -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	
	<!-- Bootstrap CSS -->
	<link href="/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	
	<link rel="stylesheet" href="/css/Classic/customAdminPanel.css">
	
	<style>
		
		.vertical-center {
			height: 100%;
			width: 100%;
			
			text-align: center;
		}
		
		.vertical-center:before {
			content: ' ';
			display: inline-block;
			
			vertical-align: middle;
			height: 100%;
		}
		
		.vertical-center > .error-404 {
			max-width: 100%;
			display: inline-block;
			vertical-align: middle;
		}
		
		.error-404 {
			margin: 0 auto;
			text-align: center;
		}
		
		.error-404 .error-code {
			bottom: 60%;
			font-size: 96px;
			line-height: 100px;
			font-weight: bold;
		}
		
		.error-404 .error-desc {
			font-size: 14px;
		}
		
		.error-404 .m-b-10 {
			margin-bottom: 10px !important;
		}
		
		.error-404 .m-b-20 {
			margin-bottom: 20px !important;
		}
		
		.error-404 .m-t-20 {
			margin-top: 20px !important;
		}
	</style>
</head>
<body>
	<div class="vertical-center">
		<div class="error-404">
			<div class="error-code m-b-10 m-t-20">404 <i class="fa fa-warning"></i></div>
			<h2 class="font-bold">Oops 404! That page can't be found.</h2>
			
			<div class="error-desc">
				Sorry, but the page you are looking for was either not found or does not exist.<br />
				Try refreshiing the page or click the button below to go back to the Homepage.<br />
				
				<div style="margin-top:10px;">
					<a href="/" class="btn btn-primary" role="button"><span class="glyphicon glyphicon-home"></span> Go back to Homepage</a>
				</div>
			</div>
		</div>
	</div>

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="/vendor/jquery/jquery.min.js"></script>
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<script src="/vendor/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>