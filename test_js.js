// Mocking the behavior for a single row
let computedGrossStr = "10000";
let festivalAllowanceStr = "500";
let profTaxStr = "200";
let dedHiddenAmtStr = "1000";

let computedGross = parseFloat(computedGrossStr) || 0;
let totalDeductions = 0;
totalDeductions += parseFloat(dedHiddenAmtStr) || 0;

let festivalAllowance = parseFloat(festivalAllowanceStr) || 0;
computedGross += festivalAllowance;

let profTax = parseFloat(profTaxStr) || 0;
totalDeductions += profTax;

let currentNetSalary = computedGross - totalDeductions;

console.log("Computed Gross:", computedGross);
console.log("Total Deductions:", totalDeductions);
console.log("Current Net Salary:", currentNetSalary);
