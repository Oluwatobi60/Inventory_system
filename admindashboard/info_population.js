
// Set current date and time by default
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('dates').value = formattedDateTime;
    });

    const assetNameInputs = document.querySelectorAll('.asset-name');
    const suggestionsLists = document.querySelectorAll('.asset-suggestions');
    const quantityInputs = document.querySelectorAll('.quantity');
    const submitButton = document.querySelector('button[name="submit-request"]');
    let availableQuantities = [];

    // Disable quantity inputs and submit button initially
    quantityInputs.forEach(input => input.disabled = true);
    submitButton.disabled = true;

    // Function to fetch asset suggestions
    async function fetchAssetSuggestions(searchTerm) {
        try {
            console.log('Fetching suggestions for:', searchTerm);
            const response = await fetch(`/admindashboard/staffallocation/search_asset.php?q=${encodeURIComponent(searchTerm)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            console.log('Raw response:', text);
            
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.log('Response that failed to parse:', text);
                return [];
            }
        } catch (error) {
            console.error('Error:', error);
            return [];
        }
    }

    // Function to check asset quantity
    async function checkAssetQuantity(assetName) {
        try {
            const response = await fetch(`/admindashboard/staffallocation/check_quantity.php?asset=${encodeURIComponent(assetName)}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            return data.quantity || 0;
        } catch (error) {
            console.error('Error checking quantity:', error);
            return 0;
        }
    }

    // Function to update form based on quantity
    async function updateFormBasedOnQuantity(assetName, index) {
        const quantity = await checkAssetQuantity(assetName);
        availableQuantities[index] = parseInt(quantity);

        // Enable/disable quantity input based on availability
        quantityInputs[index].disabled = availableQuantities[index] <= 0;

        if (availableQuantities[index] <= 0) {
            quantityInputs[index].value = '';
            quantityInputs[index].placeholder = 'Asset out of stock';
            alert('This asset is currently out of stock!');
        } else {
            quantityInputs[index].max = availableQuantities[index];
            quantityInputs[index].placeholder = `Max available: ${availableQuantities[index]}`;
        }

        return availableQuantities[index];
    }

    // Function to handle asset suggestions for both initial and new forms
    function handleAssetSuggestion(assetNameInput, suggestionsList, quantityInput, assetIndex) {
        let debounceTimer;
        
        assetNameInput.addEventListener('input', async function() {
            const searchTerm = this.value.trim();
            // Reset quantity field when asset name changes
            quantityInput.value = '';
            quantityInput.disabled = true;
            
            // Clear previous timer
            clearTimeout(debounceTimer);
            
            if (searchTerm.length > 0) {
                // Debounce the search to avoid too many requests
                debounceTimer = setTimeout(async () => {
                    try {
                        const data = await fetchAssetSuggestions(searchTerm);
                        suggestionsList.innerHTML = '';
                        suggestionsList.style.display = 'block';
                        
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(asset => {
                                const li = document.createElement('li');
                                const assetQuantity = parseInt(asset.quantity) || 0;
                                
                                li.innerHTML = `
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <span>${asset.asset_name}</span>
                                        <span class="badge ${assetQuantity > 0 ? 'quantity-available' : 'quantity-empty'}">
                                            Available: ${assetQuantity}
                                        </span>
                                    </div>
                                `;
                                
                                if (assetQuantity > 0) {
                                    li.className = 'list-group-item list-group-item-action';
                                } else {
                                    li.className = 'list-group-item list-group-item-action disabled';
                                }
                                
                                if (assetQuantity > 0) {
                                    li.addEventListener('click', function() {
                                        assetNameInput.value = asset.asset_name;
                                        document.querySelector(`#reg-no-${assetIndex}`).value = asset.reg_no;
                                        document.querySelector(`#category-${assetIndex}`).value = asset.category;
                                        document.querySelector(`#description-${assetIndex}`).value = asset.description;
                                        
                                        quantityInput.disabled = false;
                                        quantityInput.min = 1;
                                        quantityInput.max = assetQuantity;
                                        quantityInput.value = '';
                                        quantityInput.placeholder = `Max available: ${assetQuantity}`;
                                        availableQuantities[assetIndex] = assetQuantity;
                                        
                                        updateSubmitButtonState();
                                        suggestionsList.style.display = 'none';
                                    });
                                }
                                
                                suggestionsList.appendChild(li);
                            });
                        } else {
                            const li = document.createElement('li');
                            li.textContent = 'No assets found';
                            li.className = 'list-group-item';
                            suggestionsList.appendChild(li);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        suggestionsList.innerHTML = '';
                        const li = document.createElement('li');
                        li.textContent = 'Error fetching assets. Please try again.';
                        li.className = 'list-group-item text-danger';
                        suggestionsList.appendChild(li);
                    }
                }, 300);
            } else {
                suggestionsList.style.display = 'none';
            }
        });
    }

    // Initial form setup
    assetNameInputs.forEach((input, index) => {
        handleAssetSuggestion(input, suggestionsLists[index], quantityInputs[index], index);
    });

    // Add quantity input validation
    quantityInputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            const value = parseInt(this.value) || 0;
            
            if (value <= 0) {
                this.setCustomValidity('Quantity must be greater than 0');
                submitButton.disabled = true;
            } else if (value > availableQuantities[index]) {
                this.setCustomValidity(`Maximum available quantity is ${availableQuantities[index]}`);
                submitButton.disabled = true;
            } else {
                this.setCustomValidity('');
                submitButton.disabled = false;
            }
            this.reportValidity();
        });
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        assetNameInputs.forEach((input, index) => {
            if (!input.contains(e.target) && !suggestionsLists[index].contains(e.target)) {
                suggestionsLists[index].style.display = 'none';
            }
        });
    });

  
   // Handle department selection and floor population
document.getElementById('department-select').addEventListener('change', function() {
    const selectedDepartment = this.value;
    const floorSelect = document.getElementById('floor-select');
    
    console.log('Selected department:', selectedDepartment);
    
    // Clear current options and disable until data loads
    floorSelect.innerHTML = '<option value="" selected disabled>Loading floor...</option>';
    floorSelect.disabled = true;

    if (selectedDepartment) {
        fetch('/admindashboard/staffallocation/get_floor.php?department-select=' + encodeURIComponent(selectedDepartment))
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received floor data:', data);
                
                // Reset the select element
                floorSelect.innerHTML = '<option value="" selected disabled>Select Floor</option>';
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Add floor option
                if (data.floor) {
                    const option = document.createElement('option');
                    option.value = data.floor;
                    option.textContent = data.floor;
                    floorSelect.appendChild(option);
                    floorSelect.disabled = false;
                } else {
                    throw new Error('No floor found for this department');
                }
            })
            .catch(error => {
                console.error('Error fetching floor:', error);
                floorSelect.innerHTML = '<option value="" selected disabled>Error loading floor</option>';
            });
    } else {
        floorSelect.innerHTML = '<option value="" selected disabled>Select Department First</option>';
        floorSelect.disabled = true;
    }
    
    // Update form validation
    updateSubmitButtonState();
});

    // Add asset button functionality
    let assetIndex = 1; // Start index for assets
    document.getElementById('add-asset-btn').addEventListener('click', function() {
        const newAssetEntry = document.createElement('div');
        newAssetEntry.className = 'asset-entry mb-4';
        newAssetEntry.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="asset-name-${assetIndex}" class="col-form-label">Asset Name:</label>
                        <input type="text" id="asset-name-${assetIndex}" class="form-control asset-name" name="asset-name[]" placeholder="Type to search assets" autocomplete="off">
                        <ul class="asset-suggestions list-group" style="position: absolute; z-index: 1000; display: none;"></ul>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="description-${assetIndex}" class="col-form-label">Description:</label>
                        <textarea class="form-control description" id="description-${assetIndex}" name="description[]" readonly></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="unit-${assetIndex}" class="col-form-label">Asset Quantity:</label>
                        <input type="number" class="form-control quantity" id="unit-${assetIndex}" name="qty[]" min="1" placeholder="Enter quantity" disabled>
                    </div>
                </div>
               
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category-${assetIndex}" class="col-form-label">Category:</label>
                        <input type="text" class="form-control category" id="category-${assetIndex}" name="category[]" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reg-no-${assetIndex}" class="col-form-label">Registration Number:</label>
                        <input type="text" class="form-control reg-no" id="reg-no-${assetIndex}" name="reg-no[]" readonly>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-danger btn-sm remove-asset">Remove Asset</button>
                </div>
            </div>
        `;

        // Add the new asset entry to the container
        document.getElementById('assets-container').appendChild(newAssetEntry);

        // Attach event listeners using the new function
        const newAssetNameInput = newAssetEntry.querySelector(`#asset-name-${assetIndex}`);
        const newSuggestionsList = newAssetEntry.querySelector('.asset-suggestions');
        const newQuantityInput = newAssetEntry.querySelector(`#unit-${assetIndex}`);

        handleAssetSuggestion(newAssetNameInput, newSuggestionsList, newQuantityInput, assetIndex);

        assetIndex++;
    });

    // Remove asset functionality
    document.getElementById('assets-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-asset')) {
            e.target.closest('.asset-entry').remove();
        }
    });    // Function to update submit button state
    function updateSubmitButtonState() {
        const allAssetEntries = document.querySelectorAll('.asset-entry');
        const departmentSelect = document.getElementById('department-select');
        const floorSelect = document.getElementById('floor-select');
        const dateInput = document.getElementById('dates');
        let isValid = true;

        // Check if at least one asset entry exists and is valid
        if (allAssetEntries.length === 0) {
            isValid = false;
        } else {
            allAssetEntries.forEach((entry) => {
                const assetNameInput = entry.querySelector('.asset-name');
                const quantityInput = entry.querySelector('.quantity');
                const value = parseInt(quantityInput.value) || 0;
                
                if (!assetNameInput.value || !quantityInput.value || value <= 0 || quantityInput.validity.customError) {
                    isValid = false;
                }
            });
        }

        // Check other required fields
        isValid = isValid && departmentSelect.value && floorSelect.value && dateInput.value;
        submitButton.disabled = !isValid;
    }

    // Add event listeners for form validation
    document.getElementById('assets-container').addEventListener('input', function(e) {
        if (e.target.classList.contains('asset-name') || 
            e.target.classList.contains('quantity')) {
            updateSubmitButtonState();
        }
    });

    // Monitor changes to select fields and date
    document.getElementById('department-select').addEventListener('change', updateSubmitButtonState);
    document.getElementById('floor-select').addEventListener('change', updateSubmitButtonState);
    document.getElementById('dates').addEventListener('change', updateSubmitButtonState);
    document.getElementById('dates').addEventListener('input', updateSubmitButtonState);

    // Add validation check when asset suggestions are selected
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('list-group-item-action')) {
            setTimeout(updateSubmitButtonState, 100); // Small delay to ensure values are updated
        }
    })
