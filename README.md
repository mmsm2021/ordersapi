# mmsm-ordersapi
Repository for orders API and orders database

## Endpoints

- [x] GET :: /api/orders/{orderId}              
  - Returns JSON representation of order
    - If requesting user is the same as customer on the requested order or employee, admin or super
    
- [x] GET :: /api/orders/location/{locationId}
  - Takes QueryParams (sortBy, page, size) Returns all location orders sorted, on pages, with given size
    - If requesting user is employee, admin or super

- [x] GET :: /api/orders/{locationId}/last/{n}
  - Returns the last 'n' orders created for location 'locationId'
    - If requesting user is employee, admin or super

- [x] GET :: /api/orders/{userId}
  - Returns all orders for user
    - If requesting user is the same customer(userId), employee, admin or super

- [x] POST :: /api/orders
  - Creates order carried as JSON, replies with OrderID upon success
    - If user is customer, employee, admin or super

- [x] PATCH :: /api/orders/{orderId}
  - Updates order, with changes carried as JSON
    - If user is employee, admin or super

- [x] PATCH :: /api/orders/delivered/{orderId}
  - Updates orderitems marked as delivered, setting time of delivery
    - If user is employee, admin or super

- [x] DELETE :: /api/orders/{orderId}
  - Deletes the specified order
    - If user is admin or super
  - Sets orderstatus to canceled if user is customer or employee


### POST Order example JSON object
```json
{
    "location": "FranDine Bellinge",
    "locationId": "69f1b66c-e2cb-4078-a41c-e33922900d1b",
    "server": "Bernadette Harroldson",
    "items": [
        {
            "nr": 45,
            "name": "Rullekebab",
            "cost": "35"
        },
        {
            "nr": 65,
            "name": "Faxe Kondi, 0,5L",
            "cost": "15"
        }
    ],
    "discount": 20,
    "total": 40
}
```

### PATCH Order example JSON object
/api/orders/{orderId}
#### updating order items on order
```json
{
    "items": [
        {
            "itemUUID": "60a2aea33b60283dc84b4eb2",
            "nr": 30,
            "name": "Cheseburger",
            "cost": "35"
        }
    ]
}
```

### PATCH Order example JSON object
/api/orders/delivered/{orderId}
#### updating delivery status for order item
```json
{
    "items": [
        {
            "itemUUID": "60a2580b6eac0d0e0501ed5d35",
            "delivered": true
        }

    ]
}
```