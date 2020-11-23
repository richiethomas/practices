$("#admin-box-upcoming-workshops-teacher-filter").on("change", function (
  element
) {
  var $all_items = $("#admin-box-upcoming-workshops .admin-box-content li");
  var selector = this.value;
  if (selector == "*") {
    $all_items.removeClass("hide-Class");
    update_class_count_text($all_items.length);
  } else {
    var $filtered_items = $(
      '#admin-box-upcoming-workshops .admin-box-content li[data-teacher="' +
        selector +
        '"]'
    );
    $all_items
      .removeClass("hide-Class")
      .not($filtered_items)
      .addClass("hide-Class");

    update_class_count_text($filtered_items.length);
  }
});

function update_class_count_text(count) {
  $("#admin-box-upcoming-workshops-teacher-filter-class-count").text(
    count + " Classes "
  );
}

update_class_count_text(
  $("#admin-box-upcoming-workshops .admin-box-content li").length
);
