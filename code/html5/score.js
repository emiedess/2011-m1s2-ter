function jss() {
	var w, h;
	w = $(window).width();
	h = $(window).height();
	
	var mch = h/8, mnh = h*0.075;
	
	$("body, html")
		.css({
			padding: 0,
			margin: 0,
			overflow: "hidden",
			textAlign: "left"
		});
	
	$("#screen")
		.wh(w, h)
		.north($("body").north()); // TODO : par rapport à la fenêtre entière.
}

$(function () {
	var url = "score.json";
	$.getJSON(url, function(data) {
		$.each(data.scores, function(i,e) {
			var percentScore = (e.score - data.minScore) / (data.maxScore - data.minScore);
			$("#templates .scoreLine")
				.clone()
				.find(".word").text(e.name).end()
				.find(".score")
					.text(e.score)
				.css("color","rgb("+(255 - 255*percentScore).clip(0,255)+","+(191*percentScore).clip(0,255,true)+",0)")
				.end()
				.appendTo(".scores");
			jss();
		});
	});
	jss();
});