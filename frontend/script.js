const API = 'http://localhost/Test%20task%20NBP/backend/api.php';
  
// Function to load currencies into dropdown lists
function loadCurrencies() {
  fetch(API + '?currencies=true', {
    method: 'GET'
  })
    .then(response => response.json())
    .then(data => {
      const currencies = data.currencies;

      const sourceCurrencySelect = document.getElementById('sourceCurrency');
      const targetCurrencySelect = document.getElementById('targetCurrency');

      sourceCurrencySelect.innerHTML = '';
      targetCurrencySelect.innerHTML = '';

      currencies.forEach(currency => {
        const sourceOption = document.createElement('option');
        sourceOption.value = currency;
        sourceOption.textContent = currency;
        sourceCurrencySelect.appendChild(sourceOption);

        const targetOption = document.createElement('option');
        targetOption.value = currency;
        targetOption.textContent = currency;
        targetCurrencySelect.appendChild(targetOption);
      });
    })
    .catch(error => console.log(error));
}

// Function to load conversion results into the table
function loadConversionResults() {
  fetch(API, {
    method: 'POST'
  })
    .then(response => response.json())
    .then(data => {
      const conversionResults = data.conversionResults;
      const table = document.getElementById('conversionTable');

      table.innerHTML = '';

      if (conversionResults && conversionResults.length > 0) {
        const tableHeader = document.createElement('tr');
        tableHeader.innerHTML = '<th>Kwota</th><th>Waluta źródłowa</th><th>Waluta docelowa</th><th>Przeliczona Kwota</th><th>Date</th>';
        table.appendChild(tableHeader);

        conversionResults.forEach(result => {
          const row = document.createElement('tr');
          row.innerHTML = `<td>${result.amount}</td><td>${result.source_currency}</td><td>${result.target_currency}</td><td>${result.converted_amount}</td><td>${result.date}</td>`;
          table.appendChild(row);
        });
      } else {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="5">No conversion results available.</td>';
        table.appendChild(row);
      }
    })
    .catch(error => console.log(error));
}

window.addEventListener('DOMContentLoaded', () => {
  loadCurrencies();
  loadConversionResults();
});

const form = document.getElementById('conversionForm');
form.addEventListener('submit', event => {
  event.preventDefault();
  const amount = document.getElementById('amount').value;
  const sourceCurrency = document.getElementById('sourceCurrency').value;
  const targetCurrency = document.getElementById('targetCurrency').value;
  convertCurrency(amount, sourceCurrency, targetCurrency);
});

function convertCurrency(amount, sourceCurrency, targetCurrency) {
  fetch(API, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `amount=${amount}&source_currency=${sourceCurrency}&target_currency=${targetCurrency}`
  })
    .then(response => response.json())
    .then(data => {
      console.log(data);
      loadConversionResults();
    })
    .catch(error => console.log(error));
}