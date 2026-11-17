// Show only the districts of the chosen municipality.
document.querySelectorAll('[data-mun]').forEach(function (m) {
  var d = m.form && m.form.querySelector('[data-dist]'); if (!d) return;
  function sync(reset) {
    var mv = m.value;
    [].forEach.call(d.options, function (o) { if (!o.value || o.value === '0') return; o.hidden = mv !== '0' && mv !== '' && o.getAttribute('data-m') !== mv; });
    if (reset && d.selectedOptions[0] && d.selectedOptions[0].hidden) d.value = '0';
  }
  m.addEventListener('change', function () { sync(true); }); sync(false);
});
