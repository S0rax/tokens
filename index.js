(async () => {
	"use strict";

	function $(e) {
		return document.querySelectorAll(e);
	}

	const link = "./fetch.php";

	async function postValues(data) {

		let json = await fetch(link, {
			method: "POST",
			body: "data=" + JSON.stringify(data),
			headers: {
				"Content-Type": "application/x-www-form-urlencoded"
			}
		}).then((data) => {
			return data.text();
		});

		console.dir(json);
		return json;
	}

	$("form")[0].onsubmit = async e => {
		e.preventDefault();
		let ret = await postValues($("input")[0].value);
		console.log(ret.length);
		let json = JSON.parse(ret);
		// console.log(json.length);
	}
})();