#!/bin/bashw

DATA='{"included": [{"attributes": {"url": "https://mms-media-prod.s3.amazonaws.com/FOOBAR-FOO_123.jpg?AWSAccessKeyId=foobar&Expires=1553126125&x-amz-security-token=fooba%3D&Signature=foobar", "file_name": "FOO_123.jpg", "mime_type": "image/jpeg", "file_size": 226639}, "type": "media", "id": "FOOBAR-FOO_123.jpg", "links": {"self": "https://api.flowroute.com/v2.1/media/FOOBAR-FOO_123.jpg"}}, {"attributes": {"url": "https://mms-media-prod.s3.amazonaws.com/FOOBAR-BAR_123.jpg?AWSAccessKeyId=foobar&Expires=1553126125&x-amz-security-token=fooba%3D&Signature=foobar", "file_name": "BAR_123.jpg", "mime_type": "image/jpeg", "file_size": 204756}, "type": "media", "id": "FOOBAR-BAR_123.jpg", "links": {"self": "https://api.flowroute.com/v2.1/media/FOOBAR-BAR_123.jpg"}}], "data": {"relationships": {"media": {"data": [{"type": "media", "id": "FOOBAR-BAR_123.jpg"}, {"type": "media", "id": "FOOBAR-FOO_123.jpg"}]}}, "attributes": {"status": "", "body": "Some text ", "direction": "inbound", "amount_nanodollars": 9500000, "to": "11234567890", "message_encoding": 0, "timestamp": "2019-02-18T23:55:24.00Z", "delivery_receipts": [], "amount_display": "$0.0095", "from": "10987654321", "is_mms": true, "message_type": "longcode"}, "type": "message", "id": "mdr2-foobar"}}'

curl -k -v --header 'Content-Type:application/xml' -d "${DATA}" https://localhost/sms

# SMS Example
# {"data": {"attributes": {"status": "delivered", "body": "Test 4D", "direction": "inbound", "amount_nanodollars": "4000000", "message_encoding": 0, "timestamp": "2019-02-18T23:00:33.13Z", "to": "11234567890", "amount_display": "$0.0040", "from": "10987654321", "is_mms": false, "message_callback_url": "https://vera.uberzach.com/sms", "message_type": "longcode"}, "type": "message", "id": "mdr2-foobar"}}

# MMS Image Example
# {"included": [{"attributes": {"url": "https://mms-media-prod.s3.amazonaws.com/FOOBAR-572226513.jpg?AWSAccessKeyId=foobar&Expires=1553125728&x-amz-security-token=fooba%3D&Signature=c32wXzxW%2BiaVhYvwsn7ZOAGjUDo%3D", "file_name": "572226513.jpg", "mime_type": "image/jpeg", "file_size": 659749}, "type": "media", "id": "FOOBAR-572226513.jpg", "links": {"self": "https://api.flowroute.com/v2.1/media/FOOBAR-572226513.jpg"}}], "data": {"relationships": {"media": {"data": [{"type": "media", "id": "FOOBAR-572226513.jpg"}]}}, "attributes": {"status": "", "body": null, "direction": "inbound", "amount_nanodollars": 9500000, "to": "11234567890", "message_encoding": 0, "timestamp": "2019-02-18T23:48:48.00Z", "delivery_receipts": [], "amount_display": "$0.0095", "from": "10987654321", "is_mms": true, "message_type": "longcode"}, "type": "message", "id": "mdr2-foobar"}}

# MMS Text + Multiple Image Example
# {"included": [{"attributes": {"url": "https://mms-media-prod.s3.amazonaws.com/FOOBAR-FOO_123.jpg?AWSAccessKeyId=foobar&Expires=1553126125&x-amz-security-token=fooba%3D&Signature=foobar", "file_name": "FOO_123.jpg", "mime_type": "image/jpeg", "file_size": 226639}, "type": "media", "id": "FOOBAR-FOO_123.jpg", "links": {"self": "https://api.flowroute.com/v2.1/media/FOOBAR-FOO_123.jpg"}}, {"attributes": {"url": "https://mms-media-prod.s3.amazonaws.com/FOOBAR-BAR_123.jpg?AWSAccessKeyId=foobar&Expires=1553126125&x-amz-security-token=fooba%3D&Signature=foobar", "file_name": "BAR_123.jpg", "mime_type": "image/jpeg", "file_size": 204756}, "type": "media", "id": "FOOBAR-BAR_123.jpg", "links": {"self": "https://api.flowroute.com/v2.1/media/FOOBAR-BAR_123.jpg"}}], "data": {"relationships": {"media": {"data": [{"type": "media", "id": "FOOBAR-BAR_123.jpg"}, {"type": "media", "id": "FOOBAR-FOO_123.jpg"}]}}, "attributes": {"status": "", "body": "Some text ", "direction": "inbound", "amount_nanodollars": 9500000, "to": "11234567890", "message_encoding": 0, "timestamp": "2019-02-18T23:55:24.00Z", "delivery_receipts": [], "amount_display": "$0.0095", "from": "10987654321", "is_mms": true, "message_type": "longcode"}, "type": "message", "id": "mdr2-foobar"}}