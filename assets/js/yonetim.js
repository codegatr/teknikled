/* =================================================================
   TeknikLED v0.1.0 - Yonetim Paneli JS
   CODEGA - codega.com.tr
   ================================================================= */

(function () {
  'use strict';

  // ---- Mobil sidebar toggle ----
  var mobilBtn = document.getElementById('ypMobilBtn');
  var sidebar = document.getElementById('ypSidebar');
  if (mobilBtn && sidebar) {
    mobilBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      sidebar.classList.toggle('acik');
    });
    document.addEventListener('click', function (e) {
      if (window.innerWidth <= 900 && sidebar.classList.contains('acik')) {
        if (!sidebar.contains(e.target) && !mobilBtn.contains(e.target)) {
          sidebar.classList.remove('acik');
        }
      }
    });
  }

  // ---- Dil sekmeleri ----
  document.querySelectorAll('.yp-diller').forEach(function (grup) {
    var btnlar = grup.querySelectorAll('button[data-dil]');
    var idon = grup.dataset.hedef || '';
    btnlar.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        btnlar.forEach(function (b) { b.classList.remove('aktif'); });
        btn.classList.add('aktif');
        var dil = btn.dataset.dil;
        document.querySelectorAll('[data-dil-panel' + (idon ? '="' + idon + '"' : '') + ']').forEach(function (p) {
          p.classList.remove('aktif');
        });
        var hedef = document.querySelector('[data-dil-panel' + (idon ? '="' + idon + '"' : '') + '][data-dil-kod="' + dil + '"]');
        if (hedef) hedef.classList.add('aktif');
      });
    });
  });

  // ---- Resim onizleme ----
  document.querySelectorAll('input[type="file"][data-onizleme]').forEach(function (inp) {
    var hedef = document.getElementById(inp.dataset.onizleme);
    if (!hedef) return;
    inp.addEventListener('change', function () {
      if (!inp.files || !inp.files[0]) return;
      var r = new FileReader();
      r.onload = function (ev) {
        hedef.innerHTML = '<img src="' + ev.target.result + '" alt="">';
      };
      r.readAsDataURL(inp.files[0]);
    });
  });

  // ---- Ozellik editor (urun ozellikleri TR/EN/AR) ----
  window.ypOzellikEkle = function (dil) {
    var liste = document.getElementById('ozellikListe_' + dil);
    if (!liste) return;
    var div = document.createElement('div');
    div.className = 'yp-ozellik-satir';
    div.innerHTML =
      '<input type="text" name="ozellik_baslik_' + dil + '[]" placeholder="Baslik">' +
      '<input type="text" name="ozellik_deger_' + dil + '[]" placeholder="Deger">' +
      '<button type="button" class="yp-ozellik-sil" onclick="this.parentElement.remove()">×</button>';
    liste.appendChild(div);
  };

  // ---- Silme onay ----
  document.querySelectorAll('form[data-onay]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!confirm(f.dataset.onay || 'Emin misiniz?')) { e.preventDefault(); }
    });
  });

  // ---- Slug otomatik olusturma ----
  document.querySelectorAll('[data-slug-kaynak]').forEach(function (inp) {
    var hedef = document.getElementById(inp.dataset.slugKaynak);
    if (!hedef) return;
    inp.addEventListener('input', function () {
      if (!hedef.dataset.dokunuldu) {
        hedef.value = slugYap(inp.value);
      }
    });
    hedef.addEventListener('input', function () { hedef.dataset.dokunuldu = '1'; });
  });

  function slugYap(s) {
    var map = {'ı':'i','ğ':'g','ü':'u','ş':'s','ö':'o','ç':'c','İ':'i','Ğ':'g','Ü':'u','Ş':'s','Ö':'o','Ç':'c'};
    s = (s || '').toLowerCase();
    s = s.replace(/[ıİğĞüÜşŞöÖçÇ]/g, function (c) { return map[c] || c; });
    s = s.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    return s;
  }

  // ---- Galeri item sil ----
  window.ypGaleriSil = function (btn, dosya) {
    if (!confirm('Bu gorseli galeri listesinden cikarmak istediginizden emin misiniz?')) return;
    btn.parentElement.remove();
    // Hidden input'a isaret birak
    var silAlan = document.getElementById('galeriSilinenler');
    if (silAlan) {
      silAlan.value = (silAlan.value ? silAlan.value + ',' : '') + dosya;
    }
  };

})();
