(async () => {
	"use strict";

	function $(e) {
		return document.querySelectorAll(e);
	}

	async function postValues(link, data) {
		return await fetch(link, {
			method: "POST",
			body: "name=" + data,
			headers: {
				"Content-Type": "application/x-www-form-urlencoded"
			}
		}).then(data => data.json());
	}

	function getTier(length) {
		if (length < 5) {
			return 0;
		} else if (length < 12) {
			return 1;
		} else if (length < 25) {
			return 2;
		} else if (length < 50) {
			return 3;
		} else if (length < 100) {
			return 4;
		}
		return 5;
	}

	function clearList() {
		let list = $("li");
		for (let li of list) li.remove();
	}

	let active = false;

	$("form")[0].onsubmit = async e => {
		e.preventDefault();
		if (active) return;
		clearList();
		const holder = $(".holder")[0];
		const container = $(".container")[0];

		active = true;
		holder.style.opacity = 1;
		holder.style.zIndex = 10;
		container.style.filter = "blur(2px)";

		const json = await postValues("./fetch.php", $("input")[0].value);
		// console.log(json);

		$("p")[0].innerHTML = `<img src="assets/img/ok${getTier(json.length)}.png" alt="OK${(getTier(json.length))}" style="vertical-align: middle"> Your longest streak is ${json.length} tokens`;
		let str = "<th>ID</th><th>Token</th><th>Reason</th><th>Issuer</th><th>Date</th>";
		json.forEach(token => {
			const values = [
				token["id"],
				token["title"],
				token["reason"],
				token["giver_name"],
				new Date(token["event_date"] * 1e3).toLocaleString("en-GB")
			];
			str += `<tr><td>${values.join("</td><td>")}</td></tr>`;
		});
		$("table")[0].innerHTML = str;

		active = false;
		holder.style.opacity = 0;
		holder.style.zIndex = -1;
		container.style.filter = "blur(0px)";
	}

	let work = false;

	$("input")[0].onkeyup = async function(e) {
		const name = this.value;
		if (name.length < 3 || !/^[0-9a-z]+$/i.test(name) || e.key.length !== 1 || work) return;
		work = true;
		let members = await postValues("./members.php", name);
		clearList();
		const ul = $("ul")[0];
		for (let member of members) {
			const self = this;
			let li = document.createElement("li");
			li.innerHTML += member.name + "<br>" + member.extra;
			li.onclick = function() {
				self.value = this.innerText.split("\n")[0];
				self.focus();
				clearList();
			}
			ul.appendChild(li);
		}
		work = false;
	}
})();
