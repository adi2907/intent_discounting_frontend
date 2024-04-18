<div class="event-criteria-card">
    <div class="form-group">
        <label for="event-select">USERS WHO DID NOT DO THESE EVENTS:</label>
        <select id="event-select" class="form-control">
            <option value="purchase">Purchase</option>
            <option value="add_to_cart">Add to Cart</option>
            <option value="product_visit">Product Visit</option>
        </select>
    </div>

    <div class="form-group">
        <label for="occurrence-select">Number of Occurrences</label>
        <select id="occurrence-select" class="form-control">
            <option value="at_least_once">At Least Once</option>
            <option value="only_once">Only Once</option>
        </select>
    </div>
    
    <!-- Most Recent Event Time -->
    <div class="form-group">
        <label for="time-select">Most Recent Event Time</label>
        <select id="time-select" class="form-control" onchange="handleTimeSelectionChange(this)">
            <option value="yesterday">Yesterday</option>
            <option value="today">Today</option>
            <option value="within_last_days">Within Last ____ Days</option>
            <option value="before_days">Before ____ Days</option>
        </select>
    </div>

    <!-- Input for 'Within Last ____ Days' initially hidden -->
    <div id="within-last-days-container" class="form-group" style="display: none;">
        <input type="number" id="within-last-days" class="form-control" placeholder="Enter number of days">
    </div>

    <!-- Input for 'Before ____ Days' initially hidden -->
    <div id="before-days-container" class="form-group" style="display: none;">
        <input type="number" id="before-days" class="form-control" placeholder="Enter number of days">
    </div>
        
    <div class="logic-buttons">
        <button type="button" class="btn">AND</button>
        <button type="button" class="btn">OR</button>
    </div>

    <button type="button" class="btn add-event">+</button>
</div>