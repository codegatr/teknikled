/* =================================================================
   TeknikLED v0.1.0 - Public JS
   Mobil menu toggle + AJAX form gonderim
   CODEGA - codega.com.tr
   ================================================================= */

(function () {
  'use strict';

  // ---- Mobil menu toggle ----
  var menuBtn = document.getElementById('menuBtn');
  var anaMenu = document.getElementById('anaMenu');
  if (menuBtn && anaMenu) {
    menuBtn.addEventListener('click', function () {
      anaMenu.classList.toggle('acik');
      menuBtn.setAttribute('aria-expanded', anaMenu.classList.contains('acik'));
    });

    // Disa tiklayinca kapat
    document.addEventListener('click', function (e) {
      if (!anaMenu.contains(e.target) && !menuBtn.contains(e.target)) {
        anaMenu.classList.remove('acik');
      }
    });
  }

  // ---- AJAX form gonderim (iletisim + teklif) ----
  function ajaxForm(id) {
    var form = document.getElementById(id);
    if (!form) return;
    var api = form.dataset.api;
    if (!api) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var durum = form.querySelector('.form-durum');
      var btn = form.querySelector('button[type="submit"]');
      var eskiMetin = btn ? btn.innerHTML : '';

      if (btn) { btn.disabled = true; btn.innerHTML = '...'; }
      if (durum) { durum.className = 'form-durum'; durum.textContent = ''; }

      var veri = new FormData(form);

      fetch(api, {
        method: 'POST',
        body: veri,
        credentials: 'same-origin',
      })
      .then(function (r) {
        return r.json().then(function (j) { return { ok: r.ok, body: j }; });
      })
      .then(function (sonuc) {
        if (durum) {
          durum.textContent = sonuc.body.mesaj || (sonuc.ok ? 'Tamam' : 'Hata');
          durum.className = 'form-durum ' + (sonuc.ok && sonuc.body.ok ? 'ok' : 'hata');
        }
        if (sonuc.ok && sonuc.body.ok) {
          form.reset();
        }
      })
      .catch(function () {
        if (durum) {
          durum.textContent = 'Baglanti hatasi, lutfen tekrar deneyin.';
          durum.className = 'form-durum hata';
        }
      })
      .finally(function () {
        if (btn) { btn.disabled = false; btn.innerHTML = eskiMetin; }
      });
    });
  }

  ajaxForm('iletisimForm');
  ajaxForm('teklifForm');

  // ---- Hesaplayici ----
  var hesapForm = document.getElementById('hesapForm');
  if (hesapForm) {
    hesapForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var sonuc = document.getElementById('hesapSonuc');
      var btn = hesapForm.querySelector('button[type="submit"]');
      if (btn) { btn.disabled = true; }

      var veri = new FormData(hesapForm);
      var api = hesapForm.dataset.api;

      fetch(api, { method: 'POST', body: veri, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          if (!sonuc) return;
          if (!j.ok) {
            sonuc.innerHTML = '<div class="form-durum hata">' + (j.mesaj || 'Hata') + '</div>';
            return;
          }
          sonuc.innerHTML =
            '<div class="hesap-sonuc-kutu">' +
              '<h3>Hesaplama Sonucu</h3>' +
              '<table class="ozellik-tablo">' +
                '<tr><th>Piksel</th><td>' + j.piksel + '</td></tr>' +
                '<tr><th>Modul Olcusu</th><td>' + j.modul_olcu_mm + ' mm</td></tr>' +
                '<tr><th>Yatay / Dikey Modul</th><td>' + j.yatay_modul + ' x ' + j.dikey_modul + ' = ' + j.toplam_modul + ' adet</td></tr>' +
                '<tr><th>Gercek Olcu</th><td>' + j.gercek_olcu_cm + ' cm</td></tr>' +
                '<tr><th>Alan</th><td>' + j.metrekare + ' m²</td></tr>' +
                '<tr><th>Cozunurluk</th><td>' + j.cozunurluk + ' px</td></tr>' +
                '<tr><th>Ortalama Guc</th><td>' + j.ortalama_watt + ' W (max: ' + j.max_watt + ' W)</td></tr>' +
              '</table>' +
              '<p class="hesap-not">' + (j.aciklama || '') + '</p>' +
            '</div>';
        })
        .catch(function () {
          if (sonuc) sonuc.innerHTML = '<div class="form-durum hata">Baglanti hatasi</div>';
        })
        .finally(function () { if (btn) btn.disabled = false; });
    });
  }

  // ---- Galeri tikla ----
  var galeriKucuk = document.querySelectorAll('.galeri-kucuk img');
  if (galeriKucuk.length > 0) {
    galeriKucuk.forEach(function (img) {
      img.addEventListener('click', function () {
        galeriKucuk.forEach(function (i) { i.classList.remove('aktif'); });
        img.classList.add('aktif');
      });
    });
  }

  // ---- Scroll animasyonlari (fade-in-up) ----
  if ('IntersectionObserver' in window) {
    var gorunumGozleyici = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('gorundu');
          // Istatistik sayi animasyonu (hem eski .istatistik-sayi hem V3 .v3-stat-sayi)
          var sayi = entry.target.querySelector('.istatistik-sayi, .v3-stat-sayi');
          if (sayi && !sayi.dataset.oynatildi) {
            sayi.dataset.oynatildi = '1';
            sayiAnimasyon(sayi);
          }
          // Istatistik karti ise kendi sayiyisini de kontrol et
          if (entry.target.classList.contains('istatistik-kart') || entry.target.classList.contains('v3-stat')) {
            var sayi2 = entry.target.querySelector('.istatistik-sayi, .v3-stat-sayi');
            if (sayi2 && !sayi2.dataset.oynatildi) {
              sayi2.dataset.oynatildi = '1';
              sayiAnimasyon(sayi2);
            }
          }
          gorunumGozleyici.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -80px 0px' });

    document.querySelectorAll('.fade-in-up, .v3-stat').forEach(function (el) {
      gorunumGozleyici.observe(el);
    });
  } else {
    // Fallback
    document.querySelectorAll('.fade-in-up').forEach(function (el) {
      el.classList.add('gorundu');
    });
    document.querySelectorAll('.istatistik-sayi, .v3-stat-sayi').forEach(function (s) {
      sayiAnimasyon(s);
    });
  }

  function sayiAnimasyon(el) {
    var hedef = parseFloat(el.dataset.hedef || '0');
    var son = el.dataset.son || '';
    var sure = 1400;
    var baslangic = performance.now();
    function tik(simdi) {
      var gecen = simdi - baslangic;
      var t = Math.min(gecen / sure, 1);
      // easeOutExpo
      var e = t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
      var val = Math.floor(hedef * e);
      el.textContent = val + son;
      if (t < 1) requestAnimationFrame(tik);
      else el.textContent = hedef + son;
    }
    requestAnimationFrame(tik);
  }

  // Kategori kartlarinda mouse-track spotlight
  document.querySelectorAll('.kat-kart').forEach(function (kart) {
    kart.addEventListener('mousemove', function (e) {
      var rect = kart.getBoundingClientRect();
      var x = ((e.clientX - rect.left) / rect.width) * 100;
      var y = ((e.clientY - rect.top) / rect.height) * 100;
      kart.style.setProperty('--mx', x + '%');
      kart.style.setProperty('--my', y + '%');
    });
  });

})();
