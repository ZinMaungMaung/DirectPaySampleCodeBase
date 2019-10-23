<html>

    <head>

        <style>
            label {
                font-weight: bold;
            }

            .explanation {
                font-style: italic;
                font-weight: bold;
            }

            .notes {
                font-size: larger;
                font-weight: bold;
            }
        </style>

    </head>

    <body>

        <p class="notes">
            IMPORTANT: This sample code should be studied in reference to MPU Merchant Integration Guide.<br />
            As this is a sample page, the Payment Request fields are shown in visible textboxes.<br />
            In your production code, in your own Checkout page you should<br />
            - add hidden Payment Request fields (some of these will need to be dynamic, e.g. unique Invoice No, current Invoice's Amount, etc.)<br />
            - add a button (e.g. "Pay with MPU")<br />
        </p>

        <h1>MPU e-Commerce Sample Checkout Page</h1>

        <!-- <form method="post" action="{{ url('mputest.store') }}"> -->
        {!! Form::open(array('route' =>'mpu.checkout', 'id' => 'form-with-validation', 'class' => 'form-horizontal',
        "enctype"=>"multipart/form-data")) !!}
        {{ csrf_field() }}
        <label>Merchant ID:</label>
        <input type="text" id="merchantID" name="merchantID" value="204104001003623" />
        <br />
        <br />

        <label>Invoice No:</label>
        <input type="text" id="invoiceNo" name="invoiceNo" value="12345678901234567890" />
        <br />
        <span class="explanation">
            This must be a unique no that can be used to reference between your e-Commerce website and MPU Gateway.
            <br />   
            e.g. Ticket ID, Hotel Reservation ID, Sales Order/Invoice ID, etc. that is saved in your e-Commerce website DB and issued to your Customer.
            <br />
            If you need check whether the payment is successful or not, you will need to give that unique no to the Bank or MPU.
        </span> 
        <br />
        <br />

        <label>Product Desc:</label>
        <input type="text" id="productDesc" name="productDesc" value="CB Test Product" >
        <br />
        <span class="explanation">
            This will be shown to the Customer/Cardholder on MPU Payment Gateway page.
        </span>
        <br />
        <br />

        <label>Amount:</label> 
        <input type="text" id="amount" name="amount" value="000000010000" />
        <br />
        <span class="explanation">
            value in smallest currency unit (front padded with 0 to be total 12 digits)
            <br />
            e.g. 100 MMK (100 Ks) = 000000010000
            <br />
            1.50 MMK (1 Ks & 50 Pyas) = 000000000150
        </span>
        <br />
        <br />

        <label>Currency Code:</label> 
        <input type="text" id="currencyCode" name="currencyCode" value="104" />
        <br />
        <span class="explanation">
            Currently, 104 (MMK) is the only currency available.
        </span>
        <br />
        <br />

        <span class="explanation">
            These optional values (if any) will be included back in the Payment Response.
            <br />
            e.g. You can include your session variables in these fields.
        </span>
        <br /> 
        <label>User Defined 1:</label> 
        <input type="text" id="userDefined1" name="userDefined1" value="userDefined1" />
        <br />    

        <label>User Defined 2:</label> 
        <input type="text" id="userDefined2" name="userDefined2" value="userDefined2" />    
        <br />

        <label>User Defined 3:</label> 
        <input type="text" id="userDefined3" name="userDefined3" value="userDefined3" />
        <br />

        <input type="submit" value="Pay with MPU" /> 
        {!! Form::close() !!}

        <script>
            function padLeft(str, width, paddingChar) {
                paddingChar = paddingChar || '0';
                str = str + ''; // force conversion to string
                return str.length >= width ? str : new Array(width - str.length + 1).join(paddingChar) + str;
            }

            Date.prototype.yyyyMMddhhmmss = function () {
                var yyyy = this.getFullYear().toString();
                var MM = (this.getMonth() + 1).toString(); // getMonth() is zero-based
                var dd = this.getDate().toString();
                var hh = this.getHours().toString();
                var mm = this.getMinutes().toString();
                var ss = this.getSeconds().toString();
                return yyyy + padLeft(MM, 2) + padLeft(dd, 2)
                        + padLeft(hh, 2) + padLeft(mm, 2) + padLeft(ss, 2); // padding
            };

            function initPage() {
                var invoiceNo = new Date().yyyyMMddhhmmss();
                document.getElementById("invoiceNo").value = padLeft(invoiceNo, 20);
            }

            window.onload = initPage();
        </script>

    </body>

</html>