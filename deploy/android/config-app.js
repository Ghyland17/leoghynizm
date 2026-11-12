/* Aplikasi Android: alamat server FES.
   Isi FIXED dengan alamat situs Anda (mis. "https://fes.domainanda.com") lalu build ulang APK agar alamat terkunci.
   Bila kosong, aplikasi menanyakan alamat server sekali saat pertama dibuka. */
(function () {
  var FIXED = "";
  var KEY = "fes-server", s = FIXED;
  try { s = s || localStorage.getItem(KEY) || ""; } catch (e) {}
  if (s) { window.FES_API_BASE = s.replace(/\/+$/, "") + "/api/index.php"; return; }
  window.FES_NEED_SERVER = true;
  document.addEventListener("DOMContentLoaded", function () {
    var o = document.createElement("div");
    o.setAttribute("role", "dialog"); o.setAttribute("aria-modal", "true"); o.setAttribute("aria-label", "Alamat server");
    o.style.cssText = "position:fixed;inset:0;z-index:2147483647;background:#0b0d10;color:#e8eaed;display:flex;align-items:center;justify-content:center;padding:24px;font:16px/1.5 Barlow,system-ui,sans-serif";
    o.innerHTML = '<form style="width:100%;max-width:420px;display:grid;gap:14px" novalidate>' +
      '<img src="icons/icon-192.png" alt="" style="width:72px;height:72px;justify-self:center;border-radius:50%">' +
      '<h1 style="margin:0;text-align:center;font:700 24px Barlow Condensed,system-ui">Sambungkan ke server FES</h1>' +
      '<p style="margin:0;color:#9aa0a6;text-align:center">Masukkan alamat situs FES perusahaan Anda. Cukup sekali; alamat disimpan di HP ini.</p>' +
      '<label for="fes-srv" style="font-weight:600">Alamat server</label>' +
      '<input id="fes-srv" type="url" inputmode="url" autocomplete="url" placeholder="https://fes.domainanda.com" style="padding:14px;border-radius:12px;border:1px solid #3a3f47;background:#15181d;color:inherit;font:inherit">' +
      '<div id="fes-srv-err" style="color:#ff7a70;min-height:1.5em"></div>' +
      '<button type="submit" style="padding:14px;border:0;border-radius:12px;background:#eb6834;color:#fff;font:600 16px system-ui;min-height:48px">Sambungkan</button></form>';
    document.body.appendChild(o);
    var f = o.querySelector("form"), i = o.querySelector("#fes-srv"), err = o.querySelector("#fes-srv-err"), b = o.querySelector("button");
    f.addEventListener("submit", function (e) {
      e.preventDefault();
      var u = (i.value || "").trim().replace(/\/+$/, "");
      if (!/^https?:\/\//i.test(u)) u = "https://" + u;
      if (!/^https:\/\/[^\s\/]+\.[^\s\/]+/i.test(u)) { err.textContent = "Alamat harus diawali https:// dan memakai nama domain, misalnya https://fes.domainanda.com"; return; }
      b.disabled = true; b.textContent = "Memeriksa…"; err.textContent = "";
      fetch(u + "/api/index.php/health", { cache: "no-store" }).then(function (r) { return r.json(); }).then(function (j) {
        if (!j || !j.ok) throw new Error("bad");
        try { localStorage.setItem(KEY, u); } catch (e) {}
        location.reload();
      }).catch(function () {
        b.disabled = false; b.textContent = "Sambungkan";
        err.textContent = "Server tidak menjawab. Periksa alamatnya, koneksi internet, dan pastikan backend sudah dipasang.";
      });
    });
    setTimeout(function () { i.focus(); }, 100);
  });
})();
