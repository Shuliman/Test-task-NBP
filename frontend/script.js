const API = 'http://localhost/Test-task-NBP/backend/api.php';
  
function loadCurrencies() {
  fetch(API + '?currencies=true', {
    method: 'GET'
  })
    .then(response => response.json())
    .then(data => {
      //Get the list of currencies from the API response
      const currencies = data.currencies;
      //Get select items to select source and target currencies
      const sourceCurrencySelect = document.getElementById('sourceCurrency');
      const targetCurrencySelect = document.getElementById('targetCurrency');
      //Clean up the contents of select elements
      sourceCurrencySelect.innerHTML = '';
      targetCurrencySelect.innerHTML = '';

      //For each currency, create a new option element and add it to both select
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

function loadConversionResults() {
  fetch(API, {
    method: 'POST'
  })
    .then(response => response.json())
    .then(data => {
      //Get conversion results from API response
      const conversionResults = data.conversionResults;
      //Get a table item to display the conversion results
      const table = document.getElementById('conversionTable');
      //Clean up the contents of the table
      table.innerHTML = '';

      // If conversion results exist and are greater than 0, display them in the table
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

//When the DOM is fully loaded, we call the functions to load the currencies and conversion results
window.addEventListener('DOMContentLoaded', () => {
  loadCurrencies();
  loadConversionResults();
});

const form = document.getElementById('conversionForm');
form.addEventListener('submit', event => {
  // Prevent the default form submission
  event.preventDefault();
  // Get the amount, source currency, and target currency from the form
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
    // Include the amount, source currency, and target currency in the body of the request
    body: `amount=${amount}&source_currency=${sourceCurrency}&target_currency=${targetCurrency}`
  })
    .then(response => response.json())
    .then(data => {
      // Log the response data to the console
      console.log(data);
      // Reload the conversion results
      loadConversionResults();
    })
    .catch(error => console.log(error));
}
