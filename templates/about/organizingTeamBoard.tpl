{**
 * organizingTeam.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Conference index.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="about.organizingTeam"}
{include file="common/header.tpl"}
{/strip}
{foreach from=$groups item=group}
	<h4>{$group->getLocalizedTitle()}</h4>
	{assign var=groupId value=$group->getId()}
	{assign var=members value=$teamInfo[$groupId]}

	<ol class="organizingTeam">
		{foreach from=$members item=member}
			{assign var=user value=$member->getUser()}
			<li><a href="javascript:openRTWindow('{url op="organizingTeamBio" path=$user->getId()}')"><b>{$user->getFullName()|escape}</b></a>
			<br />
				{assign var="profileImage" value=$user->getSetting('profileImage')}
	{if $profileImage}
		<img height="{$profileImage.height|escape}" width="{$profileImage.width|escape}" alt="{translate key="user.profile.profileImage"}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" />
		<br />
	{/if}
			
			{if $user->getAffiliation()} {$user->getAffiliation()|escape}{/if}{if $user->getCountry()} <br /> {assign var=countryCode value=$user->getCountry()}{assign var=country value=$countries.$countryCode} {$country|escape}{/if}</li> <br />
		{/foreach}{* $members *}
	</ol>
{/foreach}{* $groups *}

{**
include file="about/conferenceSponsorship.tpl"
*}

<!---
<h3>Sponsers</h3>

<ul>
    <li><a href="http://www.njupt.edu.cn">Nanjing University of Posts and Telecommunications</a> <br /> <img src="/public/site/images/johnny/download.jpg"  width="388"></li>
    <li><a href="https://www.ntnu.edu.tw/">National Taiwan Normal University</a> <br /> <img src="/public/site/images/isdsa/logo-ntnu.png"  width="388"></li>
     <li><a href="https://www.nd.edu">University of Notre Dame</a> <br /> <img src="/public/site/images/isdsa/ndlogo.jpg"></li>
										<li><a href="http://isdsa.org/">International Society for Data Science and Analytics</a><br /> <img src="/public/site/images/isdsa/logo-isdsa.png"  width="388"></li>
</ul>    
--->
{include file="common/footer.tpl"}
