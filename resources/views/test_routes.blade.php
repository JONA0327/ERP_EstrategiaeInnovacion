<!DOCTYPE html>
<html>
<head>
    <title>Test Rutas Logística</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Prueba de Rutas de Logística</h1>
    
    <button onclick="testAduanas()">Test Check Aduanas</button>
    <button onclick="testPedimentos()">Test Check Pedimentos</button>
    <button onclick="testEmployees()">Test Search Employees</button>
    
    <div id="results"></div>
    
    <script>
        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : null;
        }
        
        function getAuthHeaders() {
            return {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            };
        }
        
        async function testAduanas() {
            try {
                const response = await fetch('/logistica/aduanas/check', {
                    method: 'GET',
                    headers: getAuthHeaders()
                });
                
                const data = await response.json();
                document.getElementById('results').innerHTML += `
                    <p><strong>Aduanas Check:</strong> ${JSON.stringify(data)}</p>
                `;
            } catch (error) {
                document.getElementById('results').innerHTML += `
                    <p><strong>Error Aduanas:</strong> ${error.message}</p>
                `;
            }
        }
        
        async function testPedimentos() {
            try {
                const response = await fetch('/logistica/pedimentos/check', {
                    method: 'GET',
                    headers: getAuthHeaders()
                });
                
                const data = await response.json();
                document.getElementById('results').innerHTML += `
                    <p><strong>Pedimentos Check:</strong> ${JSON.stringify(data)}</p>
                `;
            } catch (error) {
                document.getElementById('results').innerHTML += `
                    <p><strong>Error Pedimentos:</strong> ${error.message}</p>
                `;
            }
        }
        
        async function testEmployees() {
            try {
                const response = await fetch('/logistica/empleados/search?search=test', {
                    method: 'GET',
                    headers: getAuthHeaders()
                });
                
                const data = await response.json();
                document.getElementById('results').innerHTML += `
                    <p><strong>Employees Search:</strong> ${JSON.stringify(data)}</p>
                `;
            } catch (error) {
                document.getElementById('results').innerHTML += `
                    <p><strong>Error Employees:</strong> ${error.message}</p>
                `;
            }
        }
    </script>
</body>
</html>