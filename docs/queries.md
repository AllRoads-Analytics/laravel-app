## Query examples

```
SELECT * FROM `probable-skill-330219.pixel_events.events` WHERE DATE(ts) > "2021-10-04" LIMIT 1000;
```


### Get all pageviews with previous pages

```
SELECT ev1.uid, ev1.path, TIMESTAMP(ev1.ts) as timez,
    (SELECT ARRAY_AGG(inz.path) FROM (
        SELECT DISTINCT ev2.path
        FROM `probable-skill-330219.pixel_events.events` ev2
        WHERE ev2.uid = ev1.uid
            AND DATE(ev2.ts) > "2021-11-04"
            AND ev2.ts < ev1.ts
            AND ev2.host = 'www.timcieplowski.com'
            AND ev2.EV = "pageload"
            AND ev2.path != ev1.path
    ) inz) as prev_pages
FROM `probable-skill-330219.pixel_events.events` ev1 
WHERE DATE(ev1.ts) > "2021-11-04"
    AND ev1.host = 'www.timcieplowski.com'
    AND ev1.ev = "pageload"
ORDER BY timez asc
LIMIT 1000
;
```

### Grouped

```
SELECT outz.path, COUNT(outz.uid) as views
FROM (
    SELECT ev1.uid, ev1.path, TIMESTAMP(ev1.ts) as timez,
        (SELECT ARRAY_AGG(inz.path) FROM (
            SELECT DISTINCT ev2.path
            FROM `probable-skill-330219.pixel_events.events` ev2
            WHERE ev2.uid = ev1.uid
                AND DATE(ev2.ts) > "2021-11-04"
                AND ev2.ts < ev1.ts
                AND ev2.host = 'www.timcieplowski.com'
                AND ev2.EV = "pageload"
                AND ev2.path != ev1.path
        ) inz) as prev_pages
    FROM `probable-skill-330219.pixel_events.events` ev1 
    WHERE DATE(ev1.ts) > "2021-11-04"
        AND ev1.host = 'www.timcieplowski.com'
        AND ev1.ev = "pageload"
) outz

GROUP BY outz.path
ORDER BY views desc
```

### Join style

```
SELECT ev1.uid, ev1.path, TIMESTAMP(ev1.ts) as timez,
    ev2.uid, ev2.path, TIMESTAMP(ev2.ts) as timez2
FROM `probable-skill-330219.pixel_events.events` ev1 

    JOIN `probable-skill-330219.pixel_events.events` ev2
        ON ev2.uid = ev1.uid
        AND DATE(ev2.ts) > "2021-11-04"
        AND ev2.host = 'www.timcieplowski.com'
        AND ev2.ev = "pageload"
        AND ev2.ts < ev1.ts

WHERE DATE(ev1.ts) > "2021-11-04"
    AND ev1.host = 'www.timcieplowski.com'
    AND ev1.ev = "pageload"
    AND ev1.uid = "1-jebkb5yy-kvlccbrl"
ORDER BY timez asc
LIMIT 1000
;
```


### Join closer

```
SELECT DISTINCT outz.uid
from (
    SELECT ev1.uid, ev1.path as path_1, TIMESTAMP(ev1.ts) as timez_1,
        ev2.path as path_2, TIMESTAMP(ev2.ts) as timez_2
    FROM `probable-skill-330219.pixel_events.events` ev1 

    FULL OUTER JOIN `probable-skill-330219.pixel_events.events` ev2
            ON ev2.uid = ev1.uid
            AND DATE(ev2.ts) > "2021-11-04"
            AND ev2.host = 'www.timcieplowski.com'
            AND ev2.ev = "pageload"
            AND ev2.ts < ev1.ts
            -- AND ev2.path = '/resume/'

    WHERE DATE(ev1.ts) > "2021-11-04"
        AND ev1.host = 'www.timcieplowski.com'
        AND ev1.ev = "pageload"
        -- AND ev1.uid = "1-jebkb5yy-kvlccbrl"
    -- ORDER BY timez asc
) outz

where outz.path_1 = '/blog/2022-nba-season-preview/' -- "main page"
    and outz.path_2 = '/' -- a previous page
```


### Loop practice

```
DECLARE pages_all ARRAY<STRING(255)>;
DECLARE pages_running ARRAY<STRING(255)> DEFAULT [];
DECLARE temp_array ARRAY<STRING(255)> DEFAULT [];
DECLARE i INT64 DEFAULT 0;

SET pages_all = ['/', '/resume/', 'work'];

WHILE i < ARRAY_LENGTH(pages_all) DO
    SET pages_running = ARRAY_CONCAT( pages_running, ARRAY(SELECT pages_all[OFFSET(i)]) );
    SET i = i + 1;
END WHILE;

SELECT pages_running;
```

### I think this is working... but takes 6.3 secs to run on a tiny dataset

```
DECLARE pages_all ARRAY<STRING(255)>;
DECLARE pages_previous ARRAY<STRING(255)> DEFAULT [];
DECLARE counts ARRAY<INT64> DEFAULT [];
DECLARE current_page STRING;
DECLARE i INT64 DEFAULT 0;

SET pages_all = ['/', '/resume/', '/work/'];

CREATE TEMP TABLE history(uid STRING, path_1 STRING, path_2 STRING) AS (
    SELECT ev1.uid, ev1.path as path_1, -- TIMESTAMP(ev1.ts) as timez_1,
        ev2.path as path_2 --, TIMESTAMP(ev2.ts) as timez_2
    FROM `probable-skill-330219.pixel_events.events` ev1 

    FULL OUTER JOIN `probable-skill-330219.pixel_events.events` ev2
            ON ev2.uid = ev1.uid
            AND DATE(ev2.ts) > "2021-11-19"
            AND ev2.host = 'www.timcieplowski.com'
            AND ev2.ev = "pageload"
            AND ev2.ts < ev1.ts
            AND (
                (ev2.path is null)
                OR
                ( ev2.path in UNNEST(pages_all) )
            )

    WHERE DATE(ev1.ts) > "2021-11-19"
        AND ev1.host = 'www.timcieplowski.com'
        AND ev1.ev = "pageload"
        AND ev1.path in UNNEST(pages_all)
);

-- SELECT * FROM history;


WHILE i < ARRAY_LENGTH(pages_all) DO
    SET current_page = pages_all[OFFSET(i)];

    IF 0 = i THEN
        SET counts = [
            ( SELECT COUNT(DISTINCT uid) FROM history WHERE path_1 = current_page )
        ];
    ELSE
        SET counts = ARRAY_CONCAT( counts, [(
            WITH sub AS (
                SELECT uid, count(*) as prev_pages_viewed
                FROM history
                WHERE path_1 = current_page
                    AND path_2 IN UNNEST(pages_previous)
                GROUP BY uid
            ) SELECT COUNT(*) FROM sub WHERE prev_pages_viewed = ARRAY_LENGTH(pages_previous)
        )] );
    END IF;



    SET pages_previous = ARRAY_CONCAT( pages_previous, [ current_page ] );
    SET i = i + 1;
END WHILE;

SELECT counts;
```


### Faster, same(ish) result

```
DECLARE pages_all ARRAY<STRING(255)>;
SET pages_all = ['/', '/resume/', '/work/'];

WITH history AS (
    SELECT ev1.uid, ev1.path as path_1, -- TIMESTAMP(ev1.ts) as timez_1,
        ev2.path as path_2 --, TIMESTAMP(ev2.ts) as timez_2
    FROM `probable-skill-330219.pixel_events.events` ev1 

    FULL OUTER JOIN `probable-skill-330219.pixel_events.events` ev2
        ON ev2.uid = ev1.uid
        AND DATE(ev2.ts) > '2021-11-19'
        AND ev2.host = 'www.timcieplowski.com'
        AND ev2.ev = 'pageload'
        AND ev2.ts < ev1.ts
        AND ev2.path != ev1.path
        AND (
            (ev2.path is null)
            OR
            ( ev2.path in UNNEST(pages_all) )
        )
    WHERE DATE(ev1.ts) > '2021-11-19'
        AND ev1.host = 'www.timcieplowski.com'
        AND ev1.ev = 'pageload'
        AND ev1.path in UNNEST(pages_all)
    GROUP BY uid, path_1, path_2
)

SELECT steps_completed, COUNT(uid) AS users,
    SUM(COUNT(*)) OVER (ORDER BY steps_completed DESC) AS total_users
FROM (
    SELECT uid, count(*) steps_completed
    FROM history
    WHERE ('/' = path_1)
        OR ('/resume/' = path_1 AND '/' = path_2)
        OR ('/work/' = path_1 AND '/resume/' = path_2)
    GROUP BY uid
)
GROUP BY steps_completed 
ORDER BY steps_completed 

```
