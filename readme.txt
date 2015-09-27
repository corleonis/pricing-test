![JustPark](http://i.imgur.com/ULvqtnl.png)

# JustPark Pricing Test

## The Challenge

There is no **correct** answer to this test. Instead we will consider the efficiency, readability and overall cleanliness of the provided solution.

The challenge is to implement a class (or component) that will take an array of [Carbon](https://github.com/briannesbitt/Carbon) date instances for the time that a parking space should be booked in the following format...

```php
$input = [
    [new Carbon(..start..), new Carbon(..end..)],
    [new Carbon(..start..), new Carbon(..end..)],
    [new Carbon(..start..), new Carbon(..end..)]
];
```

... and applying a number of duration pricing rules to return a price for the available date range(s).

A base framework has been provided as a starting point. This framework includes the [Laravel IoC container](http://laravel.com/docs/ioc) a `PricingCalculator` class and a [PHPUnit](http://phpunit.de/) test file that can be used to test the class functionality.

## The Rules

| # | Rule |
| --- | --- |
| 1 | If a booking last longer than 24 hours, we no longer calculate prices in terms of hours parked. |
| 2 | If booking spans multiple days and ends before 5am, then the final day is not included in the calculation. |
| 3 | The monthly rate is used where the weekly and daily rate is more expensive. |
| 4 | The weekly rate is used where the daily rate is more expensive. |
| 5 | The daily rate is used where the hourly rate is more expensive. |

## The Pricing

| Hourly | Daily | Weekly | Monthly|
|        |       |        |        |
| £2     | £5    | £20    | £70    |

## The Examples

| # | Start | End | Duration | Cost | Notes |
| --- | --- | --- | --- | --- | --- |
| 1 | 24th Jan, 14:00 | 25th Jan, 03:00 | 1 day | £5 | Finishes before 5am. |
| 2 | 24th Jan, 14:00 | 25th Jan, 12:00 | 2 days | £10 | Finishes after 5am, so 2 days despite it being less than 24 hours. |
| 3 | 24th Jan, 14:00 | 18th Feb, 15:00 | 3 weeks and 5 days | £70 | Monthly price (£70) is less than (3 (weeks) * £20 + 5 (days) * £5) and (4 (weeks) * £20). |

## The Skills

Bonus points will be granted for the application of the following techniques and tools.

- Abstraction. We've provided a single class, you're free to use more.
- Dependency Injection. Use the Laravel container to inject dependencies through the constructor.
- Unit Testing. Transform the rules to a set of appropriate unit tests.
- Composer. Use the power of open source and the PHP community to power your project.
- Innovation. You can use our sample project, or break the mold and use different tools and techniques to solve the problem.
- Consistent Code Style. We use PSR-2.

The starting framework is a very rough one. We're sure it can be improved!

## The FAQ

Do you have a question? Email us and we'll add it to this readme.

Good luck!

![panda](http://news.worldwild.org/wp-content/uploads/2008/09/red_panda.jpg)
