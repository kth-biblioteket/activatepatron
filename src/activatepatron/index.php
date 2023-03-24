<?php
require('config.php.inc'); //innehåller API-KEY + error reporting. Byt från Sandbox vid prodsättning

session_start();
if (!isset($_SESSION['token']))
{
	$_SESSION['token'] = md5(uniqid(rand(), TRUE));
	$_SESSION['token_time'] = time();
}

if (!empty($_GET["language"])) {
			$language = $_GET["language"];
		} else {
			$language = "english";
		}
?>


<!DOCTYPE html>
<html>
	<head>
		<?php if ($language == "swedish") { ?>
		<title>Aktivera ditt bibliotekskonto</title>
		<?php } else { ?>
		<title>Activate your library account</title>
		<?php } ?>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<!-- KTH Styles -->
		<!--link href="<?php echo $kth_www?>/css/kth-22cebcf8c708b27ad9c875474470c18b.css" rel="stylesheet"-->
    	<link type="text/css" href="kthstyle/kth.css" rel="stylesheet" />
		<link type="text/css" href="css/activatepatron.css?version=1" rel="stylesheet" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery.validate.min.js"></script>
		<script type="text/javascript" src="js/jquery.placeholder.js"></script>
		<script type="text/javascript" src="js/activatepatron.js?version=1"></script>

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
	if (isset($_REQUEST['internaluser'])) { //INTERN Alma-user
		//echo $_REQUEST['internaluser'];
		header("location: /activatepatron/activatepatron_aj.php?auth=alma") ;
	} else {
		//logout
		if (isset($_REQUEST['logout'])) {
			session_unset();
			session_destroy();
			header("location: $kth_auth_endpoint/oauth2/logout");
			exit;
		}

		if(!isset($_SESSION['kth_id']))  //EXTERN KTH-User logga in via KTH-CAS
			{
				header("location: /activatepatron/login.php?language=" . $language) ;
			} else {

			}
	}
	if(isset($_SESSION['kth_id'])) {
?>	
<?php 
		if ($language == "swedish") {
			$formorderheader = "Aktivera ditt bibliotekskonto";
			$formiamlable = 'Inloggad som&nbsp';
			$formusernamelabel = 'Användarnamn';
			$formusernameplaceholder = '';
			$formagreementtext = "Jag godkänner KTH Bibliotekets <a target='_blank' href='https://www.kth.se/biblioteket/anvanda-biblioteket/anvandarvillkor-1.854843'>användarvillkor.</a>";
			$formsendordertext = 'Skicka beställningen';
			$activationtext = '<p>Ditt konto är nu aktiverat! Låna böcker med ditt svenska personnummer och din valda PIN-kod</p>
							<p>Om du inte har ett svenskt personnummer så behöver du skaffa ett lånekort i informationsdisken. Ta med dig ditt ID. Efter det kan du låna genom att använda våra låneutomater.</p>
							<p>Läs mer om hur du <a href="https://www.kth.se/biblioteket/anvanda-biblioteket/lana-och-bestalla/lana-och-bestalla-1.853035">lånar och beställer</a></p>
							<p>Välkommen till KTH Biblioteket!</p>';
			$activatedtext = '<p>Ditt konto är redan aktiverat! Låna böcker med ditt svenska personnummer och din valda PIN-kod</p>
						<p>Om du inte har ett svenskt personnummer så behöver du skaffa ett lånekort i informationsdisken. Ta med dig ditt ID. Efter det kan du låna genom att använda våra låneutomater.</p>
						<p>Läs mer om hur du <a href="https://www.kth.se/biblioteket/anvanda-biblioteket/lana-och-bestalla/lana-och-bestalla-1.853035">lånar och beställer.</a></p>
						<p>Välkommen till KTH Biblioteket!</p>';
			$pintext = 'PIN (xxxx) <i>(välj din egen fyrasiffriga PIN-kod för att låna i självbetjäningsautomaterna)</i>';
		} else {
			$formorderheader = "Activate your library account";
			$formiamlable = 'Logged in as&nbsp';
			$formusernamelabel = 'Username';
			$formusernameplaceholder = '';
			$formagreementtext = "I accept the KTH Library <a target='_blank' href='https://www.kth.se/en/biblioteket/anvanda-biblioteket/anvandarvillkor-1.854843'>terms of use</a>";
			$formsendordertext = 'Send request';
			$activationtext = '<p>Your account is now activated! Borrow books with your Swedish personal identity number and your chosen PIN code.</p>
								<p>If you don’t have a Swedish personal identity number you need to obtain a library card at the information desk. Bring your ID. After that you can borrow using our self service machines.</p>
								<p>Read more about how to <a href="https://www.kth.se/en/biblioteket/anvanda-biblioteket/lana-och-bestalla/lana-och-bestalla-1.853035">borrow and request</a></p>';
			$activatedtext = '<p>Your account is already activated! Borrow books with your Swedish personal identity number and your chosen PIN code.</p>
							<p>If you don’t have a Swedish personal identity number you need to obtain a library card at the information desk. Bring your ID. After that you can borrow using our self service machines.</p>
							<p>Read more about how to <a href="https://www.kth.se/en/biblioteket/anvanda-biblioteket/lana-och-bestalla/lana-och-bestalla-1.853035">borrow and request.</a></p>
							<p>Welcome to KTH Library!</p>';
			$pintext = 'PIN (xxxx) <i>(choose your own four digit code to borrow in the self service machines)</i>';
		}
?>
<div class="content">
	<header>
    <div class="container-fluid">
    	<div class="container">      
				<div class="header-container__top">
					<figure class="block figure defaultTheme mainLogo" data-cid="1.77257" lang="sv-SE">
						<a href="<?php echo $kth_www?>/"><img class="figure-img img-fluid" src="images/KTH_Logotyp.svg" alt="KTH:s logotyp" height="70" width="70"></a>
					</figure>
					<h1 class="block siteName" data-cid="1.260060">
          <?php if ($language == "swedish") { ?>
            <a data-cid="1.260060" href="<?php echo $kth_www?>/biblioteket">KTH Biblioteket</a>
          <?php } else {?>
            <a data-cid="1.260060" href="<?php echo $kth_www?>/en/biblioteket">KTH Library</a>
          <?php }?>
					</h1>
					<div class="block list links secondaryMenu" data-cid="1.864801" lang="sv-SE">
						<ul>
            <?php if ($language == "swedish") { ?>
							<li><a href="/activatepatron" hreflang="en-UK">English</a>
              </li>
            <?php } else {?>
              <li><a href="/activatepatron?language=swedish" hreflang="en-UK">Swedish</a>
              </li>
            <?php }?>
						</ul>
					</div>
				</div>
        <div class="header-container__bottom">
					<nav style="height: 53px;" class="block megaMenu navbar navbar-expand-lg navbar-light" data-cid="1.855134" lang="sv-SE">
						<span id="propertiesMegaMenu"></span>
						<div class="collapse navbar-collapse" id="megaMenuContent">
							<ul class="menu navbar-nav mr-auto" id="megaMenu">
								<!--  size-${policy.size} -->
								<li class="item nav-item megaItem homeItem" data-content-id="1.863181" data-id="menu-group-1-855134-27000830">
									<div class="headerItem true showLabel">
                  <?php if ($language == "swedish") { ?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/"> Hem</a>
                  <?php } else {?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/en"> Home</a>
                  <?php }?>
									</div>
									<div class="menuItemContent" id="dropdownmenu-group-1-855134-27000830">
									<div class="megaMenuBody">
										<button class="closeButton" type="button" aria-label="Stäng"></button>
										<div class="megaMenuBodyInner">
										<div id="dropdown-placeholdermenu-group-1-855134-27000830"></div>
										</div>
									</div>
									</div>
								</li>
								<li class="item nav-item megaItem" data-content-id="1.202243" data-id="menu-group-1-855134-418323064">
									<div class="headerItem false">
									<?php if ($language == "swedish") { ?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/utbildning"> Utbildning</a>
                  <?php } else {?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/en/studies"> Studies</a>
                  <?php }?>
									</div>
									<div class="menuItemContent" id="dropdownmenu-group-1-855134-418323064">
									<div class="megaMenuBody">
										<button class="closeButton" type="button" aria-label="Stäng"></button>
										<div class="megaMenuBodyInner">
										<div id="dropdown-placeholdermenu-group-1-855134-418323064"></div>
										</div>
									</div>
									</div>
								</li>
								<li class="item nav-item megaItem" data-content-id="1.202244" data-id="menu-group-1-855134-62723924">
									<div class="headerItem false">
									<?php if ($language == "swedish") { ?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/forskning"> Forskning</a>
                  <?php } else {?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/en/forskning"> Research</a>
                  <?php }?>
									</div>
									<div class="menuItemContent" id="dropdownmenu-group-1-855134-62723924">
									<div class="megaMenuBody">
										<button class="closeButton" type="button" aria-label="Stäng"></button>
										<div class="megaMenuBodyInner">
										<div id="dropdown-placeholdermenu-group-1-855134-62723924"></div>
										</div>
									</div>
									</div>
								</li>
								<li class="item nav-item megaItem" data-content-id="1.202245" data-id="menu-group-1-855134-210762362">
									<div class="headerItem false">
									<?php if ($language == "swedish") { ?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/samverkan"> Samverkan</a>
                  <?php } else {?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/en/samverkan"> Co-operation</a>
                  <?php }?>
									</div>
								</li>
								<li class="item nav-item megaItem" data-content-id="1.863186" data-id="menu-group-1-855134-1026005456">
									<div class="headerItem false">
									<?php if ($language == "swedish") { ?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/om"> Om KTH</a>
                  <?php } else {?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/en/om"> About KTH</a>
                  <?php }?>
									</div>
									<div class="menuItemContent" id="dropdownmenu-group-1-855134-1026005456">
									<div class="megaMenuBody">
										<button class="closeButton" type="button" aria-label="Stäng"></button>
										<div class="megaMenuBodyInner">
										<div id="dropdown-placeholdermenu-group-1-855134-1026005456"></div>
										</div>
									</div>
									</div>
								</li>
								<li class="item nav-item megaItem" data-content-id="1.853601" data-id="menu-group-1-855134-315160002">
									<div class="headerItem true">
									<?php if ($language == "swedish") { ?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/biblioteket"> Biblioteket</a>
                  <?php } else {?>
                    <a class="nav-link null true" href="<?php echo $kth_www?>/en/biblioteket"> Library</a>
                  <?php }?>
									</div>
									<div class="menuItemContent" id="dropdownmenu-group-1-855134-315160002">
									<div class="megaMenuBody">
										<button class="closeButton" type="button" aria-label="Stäng"></button>
										<div class="megaMenuBodyInner">
										<div id="dropdown-placeholdermenu-group-1-855134-315160002"></div>
										</div>
									</div>
									</div>
								</li>
							</ul>
						</div>
    			</nav>
				</div>
			</div>
		</div>
    <div id="gradientBorder"></div>
	</header>
	<div class="container start noMainMenu">
		<div class="row">
			<div class="col">
				<article class="article standard">
					<div id="" class="">
						<div>
							<?php 
							if ($language == "swedish") {
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
							if ($language == "swedish") {
							?>
							<div class="preArticleParagraphs">
								<div class="">
									<p>Är du inte student eller anställd på KTH? <a href="https://www.kth.se/biblioteket/anvanda-biblioteket/registrera-dig-1.869092">Registrera dig.</a></p>
								</div>
							</div>
							<?php 
							} else {
							?>
							<div class="preArticleParagraphs">
								<div class="">
									<p>Not a student or staff member at KTH? <a href="https://www.kth.se/en/biblioteket/anvanda-biblioteket/registrera-dig-1.869092">Register here.</a></p>
								</div>
							</div>
							<?php 
							}
							?>
						</div>
            <form onsubmit="return sendrequest();" method="post" action="javascript:;" name="activatepatron" id="activatepatron">
              <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
							<input id="language" name="language" type="hidden" value="<?php echo ($language)?>">
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
