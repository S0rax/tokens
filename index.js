(async () => {
	"use strict";

	function $(e) {
		return document.querySelectorAll(e);
	}

	let active = false;

	async function postValues(data) {
		return await fetch("./fetch.php", {
			method: "POST",
			body: "data=" + JSON.stringify(data),
			headers: {
				"Content-Type": "application/x-www-form-urlencoded"
			}
		}).then((data) => {
			return data.text();
		});
	}

	$("form")[0].onsubmit = async e => {
		e.preventDefault();
		if (active) return;
		let holder = $(".holder")[0];
		let container = $(".container")[0];

		active = true;
		holder.style.opacity = 1;
		holder.style.zIndex = 10;
		container.style.filter = "blur(2px)";

		let json = JSON.parse(await postValues($("input")[0].value));
		let tier, length = json.length;
		console.log(json);

		if (length < 5) {
			tier = 0;
		} else if (length < 12) {
			tier = 1;
		} else if (length < 25) {
			tier = 2;
		} else if (length < 50) {
			tier = 3;
		} else if (length < 100) {
			tier = 4;
		} else {
			tier = 5;
		}

		$("p")[0].innerHTML = `<img src="assets/img/ok${tier}.png" alt="OK${tier}" style="vertical-align: middle"> Your longest streak is ${length} tokens`;
		let str = "<th>ID</th><th>Token</th><th>Reason</th><th>Issuer</th><th>Date</th>";
		json.forEach((token) => {
			let values = [token["id"], token["title"], token["reason"], token["giver_name"], new Date(token["event_date"] * 1e3).toLocaleString("en-GB")];
			str += `<tr><td>${values.join("</td><td>")}</td></tr>`;
		})
		$("table")[0].innerHTML = str;

		active = false;
		holder.style.opacity = 0;
		holder.style.zIndex = -1;
		container.style.filter = "blur(0px)";
	}
})();