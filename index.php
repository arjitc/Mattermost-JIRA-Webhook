<?php
#Config
$bot_name = "BOT_NAME"; //eg, JIRA
$mattermost_webhook_url = "WEBHOOK_URL_HERE";
#Full path to your JIRA installation, eg, https://yourteam.atlassian.net/browse/
$jiraIssuesPath = "URL_HERE";

#Data parsed from the incoming "webhook" JIRA JSON. From: https://yourteam.atlassian.net/plugins/servlet/webhooks
$postBody = file_get_contents('php://input');
$postBodyObject = json_decode($postBody);

$jiraIssueKey = $postBodyObject->issue->key;
$jiraIssueSummary = $postBodyObject->issue->fields->summary;
$jiraWebhookEvent = $postBodyObject->webhookEvent;
$jiraIssueStatus = $postBodyObject->issue->fields->status->name; //holds "To Do", "In Progress", "Done"
$jiraCommentAuthor = $postBodyObject->comment->author->name;
$jiraCommentBody = $postBodyObject->comment->body;

#Mattermost Message 
if(empty($jiraCommentAuthor)) {
	if($jiraWebhookEvent == "jira:issue_created") {
		$message = "[$jiraIssueKey - $jiraIssueSummary]($jiraIssuesPath$jiraIssueKey) **[Created]**";
		$color = "#FF8000";
	} 
	if($jiraWebhookEvent == "jira:issue_updated") {
		$message = "[$jiraIssueKey - $jiraIssueSummary]($jiraIssuesPath$jiraIssueKey) **[$jiraIssueStatus]**";
		$color = "#249406";
	}
} else {
	$message = "[[$jiraIssueKey]($jiraIssuesPath$jiraIssueKey)] **[New Comment]** $jiraCommentBody by $jiraCommentAuthor";
	$color = "#3707ad";
}

$post = [
	'payload' => '{"username": "'.$bot_name.'", "icon_url": "ICON_URL_HERE",
	"attachments": [{
		"fallback": "'.$payloadText.'",
		"color": "'.$color.'",
		"text": "'.$payloadText.'"
	}]
}',
];

$curl_handle=curl_init();
curl_setopt($curl_handle, CURLOPT_URL, $mattermost_webhook_url);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($post));
$query = curl_exec($curl_handle);
curl_close($curl_handle);
?>
