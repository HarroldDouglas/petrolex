/**
 * Remote JS Logger - captures console.log/warn/error + uncaught errors
 * and sends them to /api/js-log for server-side debugging.
 */
(function () {
    var LOG_ENDPOINT = "/api/js-log";
    var FLUSH_INTERVAL = 3000; // flush every 3s
    var MAX_BUFFER = 100;

    var buffer = [];
    var originalConsole = {
        log: console.log.bind(console),
        warn: console.warn.bind(console),
        error: console.error.bind(console),
    };

    function stringify(args) {
        return Array.prototype.map
            .call(args, function (a) {
                if (typeof a === "string") return a;
                try {
                    return JSON.stringify(a);
                } catch (e) {
                    return String(a);
                }
            })
            .join(" ");
    }

    function push(level, args) {
        if (buffer.length >= MAX_BUFFER) buffer.shift();
        buffer.push({
            level: level,
            message: stringify(args),
            url: window.location.href,
            ts: new Date().toISOString(),
        });
    }

    // Override console methods
    console.log = function () {
        originalConsole.log.apply(console, arguments);
        push("log", arguments);
    };
    console.warn = function () {
        originalConsole.warn.apply(console, arguments);
        push("warn", arguments);
    };
    console.error = function () {
        originalConsole.error.apply(console, arguments);
        push("error", arguments);
    };

    // Catch uncaught errors
    window.addEventListener("error", function (e) {
        push("uncaught", [
            e.message + " at " + (e.filename || "?") + ":" + (e.lineno || "?"),
        ]);
    });

    // Catch unhandled promise rejections
    window.addEventListener("unhandledrejection", function (e) {
        push("rejection", [String(e.reason)]);
    });

    // Flush buffer to server
    function flush() {
        if (buffer.length === 0) return;

        var entries = buffer.splice(0, 50);

        fetch(LOG_ENDPOINT, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ entries: entries }),
        }).catch(function () {
            // silent fail - don't log to avoid infinite loop
        });
    }

    setInterval(flush, FLUSH_INTERVAL);

    // Flush on page unload
    window.addEventListener("beforeunload", function () {
        flush();
    });

    console.log("[js-logger] Remote logging active");
})();
