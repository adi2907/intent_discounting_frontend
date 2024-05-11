@php
if(isset($rule) && strlen($rule) > 0) {
    $rule = json_decode($rule, true);
}
@endphp
<div class="container user-profile-container">
    <h2 class="settings-heading">User Profile</h2>
    <div class="date-filter-section">
        <div class="userdate-option">
            <label for="lastSeen_filter" class="userdate-card-title">Last Seen</label>
            <select id="lastSeen_filter" name="lastSeen_filter" class="date-filter-select lastSeenFilterSelect">
                <option value="">Select an option</option>
                <option value="on" @if(isset($rule)) @if($rule['lastSeen_filter'] == 'on') selected @endif @endif>On</option>
                <option value="after" @if(isset($rule)) @if($rule['lastSeen_filter'] == 'after') selected @endif @endif>After</option>
                <option value="before" @if(isset($rule)) @if($rule['lastSeen_filter'] == 'before') selected @endif @endif>Before</option>
                <option value="between" @if(isset($rule)) @if($rule['lastSeen_filter'] == 'between') selected @endif @endif>Between</option>
            </select>
            <label for="lastSeen_input" class="sr-only">Last Seen Date</label>
            <input type="date" @if(isset($rule)) value="{{$rule['lastSeen_input']}}" @endif id="lastSeen_input" name="lastSeen_input" class="date-filter-input">
            <label for="lastSeen_inputEnd" class="sr-only between_top_last_date" @if(isset($rule) && $rule['lastSeen_filter'] == 'between') @else style="display: none;" @endif>Last Seen End Date</label>
            <input type="date" @if(isset($rule)) value="{{$rule['lastSeen_inputEnd']}}" @endif id="lastSeen_inputEnd" name="lastSeen_inputEnd" class="date-filter-input  between_top_last_date" @if(isset($rule) && $rule['lastSeen_filter'] == 'between') @else style="display: none;" @endif>
        </div>
    </div>
    <div class="userdate-option p-0" style="border-bottom: none;">
        <div class="userdate-option">
            <label for="createdOn_filter" class="userdate-card-title">Created On</label>
            <select id="createdOn_filter" name="createdOn_filter" class="date-filter-select createdFilterSelect">
                <option value="">Select an option</option>
                <option value="on" @if(isset($rule)) @if($rule['createdOn_filter'] == 'on') selected @endif @endif>On</option>
                <option value="after" @if(isset($rule)) @if($rule['createdOn_filter'] == 'after') selected @endif @endif>After</option>
                <option value="before" @if(isset($rule)) @if($rule['createdOn_filter'] == 'before') selected @endif @endif>Before</option>
                <option value="between" @if(isset($rule)) @if($rule['createdOn_filter'] == 'between') selected @endif @endif>Between</option>
            </select>
            <label for="createdOn_input" class="sr-only">Created On Date</label>
            <input type="date" @if(isset($rule)) value="{{$rule['createdOn_input']}}" @endif id="createdOn_input" name="createdOn_input" class="date-filter-input">
            <label for="createdOn_inputEnd" class="sr-only between_top_created_date" @if(isset($rule) && $rule['createdOn_filter'] == 'between') @else style="display: none;" @endif>Created On Date</label>
            <input type="date" @if(isset($rule)) value="{{$rule['createdOn_inputEnd']}}" @endif id="createdOn_inputEnd" name="createdOn_inputEnd" class="date-filter-input between_top_created_date" @if(isset($rule) && $rule['createdOn_filter'] == 'between') @else style="display: none;" @endif>
        </div>
    </div>
    <!-- <div class="form-group" style="display: none;">
        <label>Acquisition Source:</label>
        <div class="form-check">
            <input type="checkbox" id="organic" name="acquisition_source" value="organic" class="form-check-input" disabled="">
            <label class="form-check-label" for="organic">Organic</label>
        </div>
        <div class="form-check">
            <input type="checkbox" id="paid" name="acquisition_source" value="paid" class="form-check-input" disabled="">
            <label class="form-check-label" for="paid">Paid</label>
        </div>
    </div>
    <div class="form-group" style="display: none;">
        <label>Primary Usage:</label>
        <div class="form-check">
            <input type="checkbox" id="mobile" name="primary_usage" value="mobile" class="form-check-input" disabled="">
            <label class="form-check-label" for="mobile">Mobile</label>
        </div>
        <div class="form-check">
            <input type="checkbox" id="desktop" name="primary_usage" value="desktop" class="form-check-input" disabled="">
            <label class="form-check-label" for="desktop">Desktop</label>
        </div>
    </div> -->
    <div class="sessions-option">
        <label for="session_filter" class="sessions-card-title">Number of Sessions</label>
        <select id="session_filter" name="session_filter" class="date-filter-select">
            <option value="">Select an option</option>
            <option value="equal" @if(isset($rule)) @if($rule['session_filter'] == 'equal') selected @endif @endif>Equal to</option>
            <option value="greater_than" @if(isset($rule)) @if($rule['session_filter'] == 'greater_than') selected @endif @endif>Greater than</option>
            <option value="less_than" @if(isset($rule)) @if($rule['session_filter'] == 'less_than') selected @endif @endif>Lesser than</option>
        </select>
        <input type="number" @if(isset($rule)) value="{{$rule['session_input']}}" @endif  id="session_input" name="session_input" class="date-filter-input" placeholder="Enter number">
    </div>
</div>