$(function(){const c=function(){let t=$(".multiselect");const o=function(){const e=$(this).prop("checked");t.prop("checked",e)},l=function(){const e=$(".multiselect-header"),n=$(".multiselect"),h=n.length===n.filter(":checked").length;e.prop("checked",h)};t.each(function(){const e=$(this);e.data("id"),e.on("change",l)}),$(".multiselect-header").on("change",o)};document.addEventListener("turbo:load",c),c()});
