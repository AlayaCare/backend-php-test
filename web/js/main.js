$(document).ready(function(){
	var flashbagDescriptionDiv = $("div.flashbag");
	var deletedDescriptionText = flashbagDescriptionDiv.text();

	deletedDescriptionText = deletedDescriptionText && deletedDescriptionText.trim();
	if (deletedDescriptionText.length === 0) {
		return flashbagDescriptionDiv.fadeOut(0);
	}
	flashbagDescriptionDiv.fadeOut(2300);
});