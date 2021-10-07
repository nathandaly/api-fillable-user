### Initialise the repo
```
sail up -d
sail migrate
```

### To run the command:
```
sail artisan import:reqres-data {--dry-run: Will just dump the collection}
```

### To reset the repo
```
sail artisan migrate:fresh
```
### Todo
> If I had more time I would implement a queue based system to fetch from the API, push to redis and have a queue
> chunk through using the database cursor to avoid duplicate. I would add validation
> to avoid dodgy attributes and a better event system. 
