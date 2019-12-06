function ddmmyyyy_to_date(dateString) {
  if (!dateString) {
    return '';
  }

  var dateParts = dateString.split("/");
  var dateObject = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0]);

  return dateObject;
}
