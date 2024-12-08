export const helpers = {
  formatCentsToDecimal: function (cents) {
    const dollars = (cents / 100).toFixed(2);
    return `${dollars}`;
  },

  equals: function (a, b) {
    return a === b;
  },
  prettyPrintDate: function (isoString) {
    const date = new Date(isoString);

    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric',
      hour12: false,
      timeZoneName: 'short'
    };

    return date.toLocaleString('en-GB', options);
  }
};