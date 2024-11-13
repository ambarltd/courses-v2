export const helpers = {
  formatCentsToDecimal: function (cents) {
    const dollars = (cents / 100).toFixed(2);
    return `${dollars}`;
  },
};
