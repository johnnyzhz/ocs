{**
 * index.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Scheduled conference index page. Displayed when both a conference and a
 * scheduled conference have been specified.
 *
 * $Id$
 *}
{*
 * The page and crumb titles differ here since the breadcrumbs already include
 * the conference title, but the page title doesn't.
 *}
{strip}
{assign var="pageCrumbTitleTranslated" value=$schedConf->getSchedConfTitle()}
{assign var="pageTitleTranslated" value=$schedConf->getFullTitle()}
{include file="common/header.tpl"}
{/strip}

<h2>{$schedConf->getSetting('locationName')|nl2br}</h2>
{if $schedConf->getSetting('startDate')}
	{assign var=startDate value=$schedConf->getSetting('startDate')|date_format:$dateFormatLong}
	{assign var=endDate value=$schedConf->getSetting('endDate')|date_format:$dateFormatLong}
	<h2>{$startDate}{if $startDate != $endDate} &ndash; {$endDate}{/if}</h2>
{/if}

<br />

<div>{$schedConf->getLocalizedSetting("introduction")|nl2br}</div>


<br />

{if $homepageImage}
<div id="homepageImage"><img src="{$publicFilesDir}/{$homepageImage.uploadName|escape}" width="100%" {if $homepageImageAltText != ''}alt="{$homepageImageAltText|escape}"{else}alt="{translate key="common.conferenceHomepageImage.altText"}"{/if} /></div>
{/if}

{if $schedConfPostOverview || $schedConfShowCFP
			|| $schedConfPostPolicies || $schedConfShowProgram ||  $schedConfPostPresentations || $schedConfPostSchedule 
			|| $schedConfPostPayment  || $schedConfPostAccommodation || $schedConfPostSupporters  || $schedConfPostTimeline}
<h3>{translate key="schedConf.contents"}</h3>

<ul class="plain">
	{if $schedConfPostOverview}<li>&#187; <a href="{url page="schedConf" op="overview"}">{translate key="schedConf.overview"}</a></li>{/if}
	{if $schedConfShowCFP}
		<li>&#187; <a href="{url page="schedConf" op="cfp"}">{translate key="schedConf.cfp"}</a>{if $submissionsOpenDate} ({$submissionsOpenDate|date_format:$dateFormatLong} - {$submissionsCloseDate|date_format:$dateFormatLong}){/if}</li>
	{/if}
	{if $schedConfPostTrackPolicies}<li>&#187; <a href="{url page="schedConf" op="trackPolicies"}">{translate key="schedConf.trackPolicies"}</a></li>{/if}
	{if $schedConfShowProgram}<li>&#187; <a href="{url page="schedConf" op="program"}">{translate key="schedConf.program"}</a></li>{/if}
	{if $schedConfPostPresentations}<li>&#187; <a href="{url page="schedConf" op="presentations"}">{translate key="schedConf.presentations.short"}</a></li>{/if}
	{if $schedConfPostSchedule}<li>&#187; <a href="{url page="schedConf" op="schedule"}">{translate key="schedConf.schedule"}</a></li>{/if}
	{if $schedConfPostPayment}<li>&#187; <a href="{url page="schedConf" op="registration"}">{translate key="schedConf.registration"}</a></li>{/if}
	{if $schedConfPostAccommodation}<li>&#187; <a href="{url page="schedConf" op="accommodation"}">{translate key="schedConf.accommodation"}</a></li>{/if}
	{if $schedConfPostSupporters}<li>&#187; <a href="{url page="about" op="organizingTeam"}">{translate key="schedConf.supporters"}</a></li>{/if}
	{if $schedConfPostTimeline}<li>&#187; <a href="{url page="schedConf" op="timeline"}">{translate key="schedConf.timeline"}</a></li>{/if}
</ul>
{/if}

<!---
<h3>ANNOUNCEMENTS</h3>

<ul>
    <li><a href="/index.php/isdsa/2019/announcement/view/1">Best paper awards</a></li>
	<li><a href="/index.php/isdsa/2019/announcement/view/2">Conference proceedings</a></li>
	<li><a href="/index.php/isdsa/2019/announcement/view/4">Presentation guidelines</a></li>
</ul>
--->

{if $enableAnnouncementsHomepage}
	{* Display announcements *}
	<div id="announcementsHome">
		<h3>{translate key="announcement.announcementsHome"}</h3>
		{include file="announcement/list.tpl"}	
		<table class="announcementsMore">
			<tr>
				<td><a href="{url page="announcement"}">{translate key="announcement.moreAnnouncements"}</a></td>
			</tr>
		</table>
	</div>
{/if}

{if $showConfiguredSponsorship}
{include file="about/conferenceSponsorship.tpl"}
{else}
{* Keep the existing sponsor logos visible until sponsor records are configured in OCS. *}
<h3>Sponsors</h3>

<ul>
    <li><a href="https://bnupsych.bnu.edu.cn/">Faculty of Psychology, Beijing Normal University</a> <br /> <a href="https://bnupsych.bnu.edu.cn/"><img src="{$baseUrl}/public/site/images/isdsa/logo-bnu.png" alt="Faculty of Psychology, Beijing Normal University"></a></li>
	
	<li><a href="http://www.njupt.edu.cn">Nanjing University of Posts and Telecommunications</a> <br /> <a href="http://www.njupt.edu.cn"><img src="{$baseUrl}/public/site/images/johnny/download.jpg" alt="Nanjing University of Posts and Telecommunications" width="388"></a></li>
	
	
<li><a href="https://www.mgt.ntnu.edu.tw/">College of Management, National Taiwan Normal University</a> <br /> <a href="https://www.mgt.ntnu.edu.tw/"><img src="{$baseUrl}/public/site/images/isdsa/logo-ntnu.png" alt="College of Management, National Taiwan Normal University" width="388"></a></li>

<!--
<li><a href="https://mdpi.com/journal/computation" itemprop="url" target="_blank">
      <img src="/img/computation.png" width="388" alt="Computation">
    </a></li>


     <li><a href="http://en.ias.fudan.edu.cn/">Fudan Institute for Advanced Study in Social Sciences, Fudan University</a><br /> <a href="http://en.ias.fudan.edu.cn/"><img src="https://meeting.isdsa.org/img/fias.jpg"></a></li>
	 <li><a href="https://smep.org/">Society of Multiple Experimental Psychology</a><br /> <a href="https://smep.org/"><img src="/img/smep.jpg"></a></li>
	 
-->
	 
	<li><a href="https://isdsa.org/">International Society for Data Science and Analytics</a><br /> <a href="https://isdsa.org/"><img src="{$baseUrl}/public/site/images/isdsa/logo-isdsa.png" alt="International Society for Data Science and Analytics" width="388"></a></li>
</ul>

<p>
	<b>Sponsoring Journal</b> <br />
	Selected papers will be published free of charge in the <a href="https://jbds.isdsa.org/">Journal of Behavioral Data Science</a>.<br /> <br />
<!--
	and the journal <a href="https://www.mdpi.com/journal/computation">Computation</a> after peer review.<br /> <br />
-->	
	</p>
{/if}

{if !empty($supportName) || !empty($supportPhone) || !empty($supportEmail)}
<div id="supportContact">
	<h3>{translate key="about.contact.supportContact"}</h3>
	<p>
		{if !empty($supportName)}<strong>{$supportName|escape}</strong><br />{/if}
		{if !empty($supportPhone)}{translate key="about.contact.phone"}: {$supportPhone|escape}<br />{/if}
		{if !empty($supportEmail)}{translate key="about.contact.email"}: {mailto address=$supportEmail|escape encode="hex"}<br />{/if}
	</p>
</div>
{/if}

{$additionalHomeContent}

{include file="common/footer.tpl"}
