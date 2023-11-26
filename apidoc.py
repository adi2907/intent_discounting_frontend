[9:19 AM, 11/25/2023] Aditya Ganguli: API DOCUMENTATION FOR visit/user/session/cart count APIs
1. Default Use Case (Previous Day)
GET https://almeapp.com/analytics/[API_TYPE]_count?app_name=[YourAppName]
2. Specific Number of Previous Days
GET https://almeapp.com/analytics/[API_TYPE]_count?app_name=[YourAppName]&days=<number_of_days>
Parameters: days - Number of days to look back from the current date.
3. Specific Date Range
GET https://almeapp.com/analytics/session_count?app_name=[YourAppName]&days=3
Parameters: start_date - Start date of the range, end_date - End date of the range.


Response Format:
{
  "session_count": number,
  "app_name": string
}
[9:19 AM, 11/25/2023] Aditya Ganguli: path('visit_conversion/', VisitConversionView.as_view(), name='visitConversion'),
    path('cart_conversion/', CartConversionView.as_view(), name='cartConversion'),
    path('purchase_conversion/', PurchaseConversionView.as_view(), name='purchaseConversion'),

    API DOCUMENTATION FOR CONVERSION APIs
Endpoint: https://almeapp.com/analytics/visit_conversion


Parameters:
days (optional, integer): Number of past days to include (default: 1).
Response: JSON with dates as keys and objects containing purchases, total_sessions, and conversion_rate.
Example Request: https://almeapp.com/analytics/visit-conversion?app_name=[YourAppName]&days=3

Response Format:
{
  "YYYY-MM-DD": {
    "purchases": number,
    "total_sessions": number,
    "conversion_rate": percentage
  },
  ...
}

'''

path('product_visits/', ProductVisitsView.as_view(), name='productVisits'),
    path('product_cart_conversion/', ProductCartConversionView.as_view(), name='productCart'),

API DOCUMENTATION FOR PRODUCT VISITS API
Endpoint: https://almeapp.com/analytics/product_visits

Parameters:
- start_date (optional, string): Start date for filtering visits in YYYY-MM-DD HH:MM:SS format. Defaults to the beginning of the previous day.
- end_date (optional, string): End date for filtering visits in YYYY-MM-DD HH:MM:SS format. Defaults to the end of the previous day.
- app_name (optional, string): Application name for filtering visits.
- order (optional, string): Sort order for visit counts, either 'asc' for ascending or 'desc' for descending. Default is 'desc'.

Example Request:
https://almeapp.com/analytics/product_visits?app_name=[YourAppName]&token=[YourToken]&start_date=YYYY-MM-DD 00:00:00&end_date=YYYY-MM-DD 23:59:59&order=desc

Example Request for Default Date Range (Previous Day):
https://almeapp.com/analytics/product_visits?app_name=[YourAppName]&token=[YourToken]

Response Format:
[
  {
    "item__name": string,
    "item__product_id": string,
    "visit_count": number
  },
  ...
]

Notes:
- The response is a list of objects, each containing the name and product ID of the item, along with the count of visits.
- The results are sorted by visit count, in either ascending or descending order as specified by the 'order' parameter.