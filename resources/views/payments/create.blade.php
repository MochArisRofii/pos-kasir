@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Process Payment</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Transaction:</strong> #{{ $transaction->transaction_code }}<br>
                        <strong>Total Amount:</strong> <span class="fs-5 fw-bold">Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</span>
                    </div>

                    <form action="{{ route('payments.store', $transaction) }}" method="POST" id="paymentForm">
                        @csrf
                        
                        <!-- Input hidden untuk nilai numerik -->
                        <input type="hidden" name="amount_numeric" id="amount_numeric" value="{{ $transaction->final_amount }}">
                        
                        <div class="mb-3">
                            <label for="method" class="form-label">Payment Method</label>
                            <select class="form-select" id="method" name="method" required>
                                <option value="cash" selected>Cash</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount Paid</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="amount" 
                                       value="{{ number_format($transaction->final_amount, 0, ',', '.') }}" 
                                       required
                                       oninput="formatCurrency(this)">
                            </div>
                            <small class="text-muted">Enter the amount received from customer</small>
                        </div>

                        <div id="cash-change" class="alert alert-info" style="display: none;">
                            <strong>Change: </strong><span id="change-amount" class="fw-bold">Rp 0</span>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                Process Payment
                            </button>
                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-secondary">
                                Back to Transaction
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Change -->
<div class="modal fade" id="changeModal" tabindex="-1" aria-labelledby="changeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="changeModalLabel">Payment Successful</h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h4>Payment Completed</h4>
                <p class="mb-1">Total: <strong>Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</strong></p>
                <p class="mb-1">Amount Paid: <strong id="modalAmountPaid">Rp 0</strong></p>
                <p class="mb-3">Change: <strong id="modalChangeAmount" class="text-success">Rp 0</strong></p>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Format currency input dengan titik
function formatCurrency(input) {
    // Simpan posisi cursor
    let cursorPosition = input.selectionStart;
    
    // Remove non-numeric characters
    let value = input.value.replace(/[^\d]/g, '');
    
    // Format dengan thousand separators
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    
    input.value = value;
    
    // Kembalikan posisi cursor (adjust untuk tambahan karakter titik)
    let newCursorPosition = cursorPosition;
    if (value.length > input.value.length) {
        newCursorPosition -= 1;
    } else if (value.length < input.value.length) {
        newCursorPosition += 1;
    }
    input.setSelectionRange(newCursorPosition, newCursorPosition);
    
    calculateChange();
}

// Get numeric value dari formatted string
function getNumericValue(formattedValue) {
    if (!formattedValue) return 0;
    return parseInt(formattedValue.replace(/[^\d]/g, '')) || 0;
}

function calculateChange() {
    const method = document.getElementById('method').value;
    const formattedAmount = document.getElementById('amount').value;
    const amountPaid = getNumericValue(formattedAmount);
    const totalAmount = {{ $transaction->final_amount }};
    
    const changeElement = document.getElementById('cash-change');
    const changeAmountElement = document.getElementById('change-amount');
    const submitBtn = document.getElementById('submitBtn');
    const amountInput = document.getElementById('amount');
    const amountNumericInput = document.getElementById('amount_numeric');
    
    // Update hidden input dengan nilai numerik
    amountNumericInput.value = amountPaid;
    
    // Reset validation
    amountInput.classList.remove('is-invalid');
    submitBtn.disabled = false;
    
    if (method === 'cash') {
        if (amountPaid > totalAmount) {
            const change = amountPaid - totalAmount;
            changeAmountElement.textContent = 'Rp ' + change.toLocaleString('id-ID');
            changeElement.style.display = 'block';
            changeElement.className = 'alert alert-info';
        } else if (amountPaid === totalAmount) {
            changeAmountElement.textContent = 'Rp 0';
            changeElement.style.display = 'block';
            changeElement.className = 'alert alert-info';
        } else {
            changeElement.style.display = 'none';
        }
        
        // Validasi amount
        if (amountPaid < totalAmount) {
            amountInput.classList.add('is-invalid');
            submitBtn.disabled = true;
        }
    } else {
        changeElement.style.display = 'none';
    }
}

// Handle form submission
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const formattedAmount = document.getElementById('amount').value;
    const amountPaid = getNumericValue(formattedAmount);
    const totalAmount = {{ $transaction->final_amount }};
    
    // Validasi final sebelum submit
    if (amountPaid < totalAmount) {
        e.preventDefault();
        alert('Amount paid must be greater than or equal to total amount!');
        return;
    }
    
    const change = amountPaid - totalAmount;
    
    // Update modal content
    document.getElementById('modalAmountPaid').textContent = 'Rp ' + amountPaid.toLocaleString('id-ID');
    document.getElementById('modalChangeAmount').textContent = 'Rp ' + change.toLocaleString('id-ID');
    
    // Show modal
    const changeModal = new bootstrap.Modal(document.getElementById('changeModal'));
    changeModal.show();
    
    // Prevent default form submission
    e.preventDefault();
    
    // Submit form after modal is shown
    setTimeout(() => {
        this.submit();
    }, 2000);
});

// Initialize
document.getElementById('method').value = 'cash';
calculateChange();

// Add event listeners
document.getElementById('amount').addEventListener('blur', calculateChange);
document.getElementById('amount').addEventListener('focus', function() {
    this.select();
});
</script>

<style>
.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3cpath d='M6 7v2.5'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
.input-group-text {
    background-color: #f8f9fa;
}
</style>
@endsection