<?php
require('config.php.inc'); //innehåller API-KEY + error reporting. Byt från Sandbox vid prodsättning

session_start();
if (!isset($_SESSION['token']))
{
	$_SESSION['token'] = md5(uniqid(rand(), TRUE));
	$_SESSION['token_time'] = time();
}

if (!empty($_GET["lang"])) {
			$language = $_GET["lang"];
		} else {
			$language = "en";
		}
?>


<!DOCTYPE html>
<html>
	<head>
		<?php if ($language == "sv") { ?>
		<title>Aktivera ditt bibliotekskonto</title>
		<?php } else { ?>
		<title>Activate your library account</title>
		<?php } ?>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<!-- KTH Styles -->
		<!--link href="<?php echo $kth_www?>/css/kth-22cebcf8c708b27ad9c875474470c18b.css" rel="stylesheet"-->
    	<link type="text/css" href="kthstyle/kth.css" rel="stylesheet" />
		<link type="text/css" href="css/activatepatron.css?version=1.2" rel="stylesheet" />
		<link type="text/css" href="css/activatepatron_kiosk.css?version=1.2" rel="stylesheet" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery.validate.min.js"></script>
		<script type="text/javascript" src="js/jquery.placeholder.js"></script>
		<script type="text/javascript" src="js/activatepatron.js?version=1"></script>

		<!-- Figtree -->
		<link rel="preconnect" href="https://fonts.googleapis.com">
    	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    	<link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

		<!-- Matomo -->
		<script>
		var _paq = window._paq = window._paq || [];
		/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
		_paq.push(['trackPageView']);
		_paq.push(['enableLinkTracking']);
		(function() {
			var u="https://analytics.sys.kth.se/";
			_paq.push(['setTrackerUrl', u+'matomo.php']);
			_paq.push(['setSiteId', '7']);
			var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
			g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
		})();
		</script>
		<!-- End Matomo Code -->

	</head>
	<body>
<?php
	if (!isset($_SESSION)) {
		session_start();
	}
	if (isset($_REQUEST['auth'])) { //Ej KTH SSO
		header("location: /activatepatron/activatepatron_aj_kiosk.php?auth=" . $_REQUEST['auth'] . "&lang=" . $language) ;
	} else {
		//logout
		if (isset($_REQUEST['logout'])) {
			session_unset();
			session_destroy();
			header("location: /activatepatron/index_kiosk.php?auth=ldap&lang=" . $language) ;
			exit;
		}

		if(!isset($_SESSION['kth_id'])) {
			header("location: /activatepatron/activatepatron_aj_kiosk.php?auth=" . $_REQUEST['auth'] . "&lang=" . $language) ;
		} else {

		}
	}
	if(isset($_SESSION['kth_id'])) {
?>	
<?php 
		if ($language == "sv") {
			$formorderheader = "Aktivera ditt bibliotekskonto";
			$formiamlable = 'Inloggad som&nbsp';
			$formusernamelabel = 'Användarnamn';
			$formusernameplaceholder = '';
			$formagreementtext = "Jag godkänner KTH Bibliotekets användarvillkor";
			$formsendordertext = 'Skicka beställningen';

			$activationtext = '<p>Ditt konto är aktiverat! Låna böcker med ditt svenska personnummer eller det tillfälliga T-personnumret och din valda PIN-kod i våra självserviceautomater.</p>
								<p>Välkommen till KTH Biblioteket!</p>';

			$activatedtext = '<p>Ditt konto är redan aktiverat! Låna böcker med ditt svenska personnummer eller det tillfälliga T-personnumret och din valda PIN-kod i våra självserviceautomater.</p>
								<p>Välkommen till KTH Biblioteket!</p>';

			$pintext = 'PIN (xxxx) <i>(välj din egen fyrasiffriga PIN-kod för att låna i självbetjäningsautomaterna)</i>';
			$userterms = "<h1>Användarvillkor</h1><p>Genom att aktivera ditt bibliotekskonto ingår du ett avtal med biblioteket och förbinder dig att följa nedanstående villkor.<br>KTH Biblioteket är öppet för alla men vissa tjänster är förbehållna för studenter och personal på KTH.</p><h2>Bibliotekskonto</h2><p>För att låna och reservera material behöver du ett aktivt låntagarkonto. Folkbokförda i Sverige som fyllt 16 år kan få bibliotekskonto på KTH Biblioteket. Bibliotekskontot är personligt och får inte överlåtas till någon annan. Om du tappar bort ditt kort ska du anmäla det till biblioteket så snabbt som möjligt.</p><h2>Lån</h2><ul><li>Du ansvarar personligen för dina lån tills dessa är avregistrerade.</li><li>Vid lån för annans räkning krävs fullmakt.</li><li>Du är själv skyldig att hålla reda på lånetider.</li><li>Som en service till dig skickar biblioteket ut påminnelser om detta. Du ansvarar för att biblioteket har en korrekt e-postadress till dig.</li><li>Lån som inte längre kan förlängas ska återlämnas senast angivet datum. Tänk på detta t.ex. i samband med resor.</li><li>I vissa fall tas en förseningsavgift ut enligt prislista på webbplatsen. Du blir spärrad för vidare lån om du har för höga förseningsavgifter.</li><li>Du är skyldig att ersätta skadat eller borttappat material. Alla lagningar görs av biblioteket.</li><li>Har material inte återlämnats 17 dagar efter återlämningsdatum spärras du från vidare lån och faktura skickas ut. Fakturan avser dels kostnaden för ersättningsexemplar och dels bibliotekets extra kostnader för återanskaffning. Vi accepterar även att du köper in ett eget ersättningsexemplar som du lämnar till biblioteket.</li></ul><h2>Personuppgifter</h2><ul><li>KTH Biblioteket behandlar dina personuppgifter för att du ska kunna låna och reservera böcker och annat biblioteksmaterial.&shy;&shy;</li><li>Uppgift som rör enskild persons lån, reservation eller annan form av beställning är sekretesskyddad.</li><li>Om du är knuten till KTH som student eller personal hämtas dina personuppgifter från KTH:s centrala register. Om du avslutar din anställning eller dina studier tas dina uppgifter efter en tid automatiskt bort ur bibliotekets system.</li><li>Om du är extern låntagare anger du dina egna uppgifter när du ansöker om lånekort. Om du behöver ändra någon uppgift eller vill bli borttagen som låntagare kontaktar du KTH Biblioteket. Om du slutar använda KTH Biblioteket tas dina uppgifter efter en tid automatiskt bort ur bibliotekets system.</li></ul>";
		} else {
			$formorderheader = "Activate your library account";
			$formiamlable = 'Logged in as&nbsp';
			$formusernamelabel = 'Username';
			$formusernameplaceholder = '';
			$formagreementtext = "I accept the KTH Library terms of use";
			$formsendordertext = 'Send request';

			$activationtext = '<p>Your account is activated! Borrow books using your Swedish personal identification number or the temporary T-personal identification number and your chosen PIN code at our self-service machines.</p>
								<p>Welcome to KTH Library!</p>';

			$activatedtext = '<p>Your account is already activated!  Borrow books using your Swedish personal identification number or the temporary T-personal identification number and your chosen PIN code at our self-service machines.</p>
								<p>Welcome to KTH Library!</p>';
								
			$pintext = 'PIN (xxxx) <i>(choose your own four digit code to borrow in the self service machines)</i>';
			$userterms = "<h1>Terms of use</h1><p>When using the KTH Library services you agree to the following regulations. By activating the library account, you enter into a formal agreement with KTH Library, consenting to abide by KTH Library regulations. <br>KTH Library is open to the public. Certain services and collections are available only to KTH students and staff.</p><h2>Library account</h2><p>Residents of Sweden who have turned 16 years of age are eligible to apply for a KTH library account. Non-resident researchers and students are also eligible to apply for a KTH Library account. The library account is personal and not transferable to anyone else. In the event of a card being lost or stolen, you should notify the library as soon as possible.</p><h2>Loans</h2><ul><li>You are personally responsible for your loans until these are registered as returned.</li><li>&nbsp;Loans made on behalf of others require written authorization.</li><li>&nbsp;It's your responsibility to keep track of due dates.</li><li>The library is sending out reminders as a service. It is your responsibility to make sure that the library has a valid e-mail account registered.</li><li>Loans should be returned immediately on the due date. Borrowed material must be kept available for immediate return if recalled. Keep this in mind if you are planning to travel.</li><li>An overdue fee is charged for certain books that are not returned by due date. If your overdue fees are too excessive, your account will be inhibited. You are liable to pay for damages of the material. All repairs to damaged material are to be carried out by KTH Library.</li><li>If material has not been returned by 17 days after due date, your account will be inhibited and issued with an invoice. The invoice covers the cost of the replacement material itself plus any extra cost incurred by the library in procuring the replacement material. We also accept replacement copies</li></ul><h2>Processing of personal data</h2><ul><li>The KTH Library processes your personal data in order for you to borrow and request books and other library material.</li><li>If you are a member of KTH as a student or staff, your personal data will be retrieved from KTH's central register. If you finish your employment or studies, your data will automatically be removed from the library system after a while.</li><li>If you are a member of the public, you enter your own data when applying for a library account. If you need to change any information or want to be removed as a borrower, please contact the KTH Library. If you stop using the KTH Library, your data will be automatically removed from the library system after a while.</li></ul>";
		}
?>
<div class="content">
	<div class="container start noMainMenu">
		<div class="row">
			<div class="col">
				<article class="article standard">
					<div id="" class="">
						<div>
							<?php 
							if ($language == "sv") {
							?>
							<div class="preArticleParagraphs">
								<h1>Aktivera ditt låntagarkonto</h1>
								<div class="lead ">
									<p>För att kunna reservera, låna eller beställa material från biblioteket behöver ditt låntagarkonto aktiveras. Aktivera ditt konto genom att godkänna våra användarvillkor nedan.</p>
								</div>
							</div>
							<?php 
							} else {
							?>
							<div class="preArticleParagraphs">
								<h1>Activate your library account</h1>
								<div class="lead ">
									<p>In order to borrow or request materials from the library you need to activate your library account. Activate your account by accepting our terms of use below.</p>
								</div>
							</div>
							<?php 
							}
							?>
						</div>

						<?php //echo $formorderheader; ?>
            			<div style="">
							<?php 
							if ($language == "sv") {
							?>
							
							<?php 
							} else {
							?>
							
							<?php 
							}
							?>
						</div>
						<form onsubmit="return sendrequest();" method="post" action="javascript:;" name="activatepatron" id="activatepatron">
							<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
							<input id="lang" name="lang" type="hidden" value="<?php echo ($language)?>">
							<div id="userinfo">
								<span style="display:inline-block" class="" for="userfullname"><?php echo $formiamlable; ?></span><i><span id="userfullname"></span></i>
								<div id="activepatron"></div>
								<div id="activepatroninfo"><?php //echo $activepatroninfo;?><p><?php //echo $nyinfo;?></p></div>
							</div>
							<input id="iskth" name="iskth" type="hidden" value="true">
							<input id="id" name="id" type="hidden" value="<?php echo $_SESSION['kth_id'] ?>" class="required" placeholder="">
							<input id="almaid" name="almaid" type="hidden" value="">
							<input id="isactivepatron" name="isactivepatron" type="hidden" value="">
							<div id="almapin">
								<label style="" class="bib_label" for="almapin">
									<?php echo $pintext;?>
								</label>
							<div>
							<input class="required" type="text" name="almapinnumber" id="almapinnumber" minlength="2">
							</div>
							</div>
							<div id="willing">
								<input class="required" type="checkbox" name="willingcheck" id="willingcheck" value="J">&nbsp;<label style="display:inline-block" class="bib_label" for="willingcheck"><?php echo$formagreementtext?></label><br>
							</div>
							<div>
								<input id="skicka" name="skicka" type="submit" value="" >
							</div>
						</form>
						<div id="myModal" class="1modal" tabindex="-1">
							<div class="1modal-content">
								<div id="loadingmessage">
									<img src="images/ajax_loader_blue_512.gif"/>
									<div id="modaltext" class="alert alert-danger"></div>
								</div>
							</div>
            			</div>
						<div class="activatedtext alert alert-info">
							<?php echo $activatedtext;?>
						</div>
						<div class="activationtext alert alert-success">
							<?php echo $activationtext;?>
						</div>
						<?php echo $userterms;?>
					</div>
				</article>
			</div>
		</div>
	</div>
</div>
<?php
	}
?>
	</body>
<style>
</style>
</html>
