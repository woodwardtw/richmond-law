// Add your JS customizations here
document.addEventListener('facetwp-loaded', function() {
    document.querySelectorAll('.facetwp-facet').forEach(function(facet) {
        facet.querySelectorAll('.facetwp-display-value').forEach(function(el) {
            el.textContent = el.textContent.replace(/^term-/, '');
        });
    });
});