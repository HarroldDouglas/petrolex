class ReverbClient {
    constructor(appKey, options = {}) {
        this.appKey = appKey;
        this.options = {
            wsHost: options.wsHost || "127.0.0.1",
            wsPort: options.wsPort || (typeof window !== "undefined" && window.location.protocol === "https:" ? 443 : 8080),
            ...options,
        };
        this.state = "initialized";
        this.channels = new Map();
        this.callbacks = new Map();
        this.socketId = null;
        this.connect();
    }

    connect() {
        // Auto-détection du protocole WebSocket basé sur le protocole de la page
        const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const url = `${wsProtocol}//${this.options.wsHost}:${this.options.wsPort}/app/${this.appKey}?protocol=7&client=js&version=8.3.0&flash=false`;
        console.log("🔗 ReverbClient connecting to:", url);

        this.ws = new WebSocket(url);

        this.ws.onopen = () => {
            this.setState("connecting");
        };

        this.ws.onmessage = (event) => {
            const message = JSON.parse(event.data);
            this.handleMessage(message);
        };

        this.ws.onerror = (error) => {
            console.error("❌ ReverbClient error:", error);
            this.setState("failed");
        };

        this.ws.onclose = (event) => {
            console.log("🔒 ReverbClient closed:", event.code, event.reason);
            this.setState("disconnected");
        };
    }

    handleMessage(message) {
        console.log("📨 ReverbClient message:", message);

        // Répondre automatiquement aux pings
        if (message.event === "pusher:ping") {
            this.send({
                event: "pusher:pong",
                data: {},
            });
            console.log("🏓 Pong envoyé");
            return;
        }

        if (message.event === "pusher:connection_established") {
            this.socketId = JSON.parse(message.data).socket_id;
            this.setState("connected");
            this.trigger("connected");
        } else if (message.channel) {
            // Message sur un canal spécifique
            const channel = this.channels.get(message.channel);
            if (channel) {
                channel.trigger(
                    message.event,
                    JSON.parse(message.data || "{}"),
                );
            }
        }
    }

    setState(newState) {
        const previousState = this.state;
        this.state = newState;
        console.log(`🔄 ReverbClient: ${previousState} → ${newState}`);
        this.trigger("state_change", {
            previous: previousState,
            current: newState,
        });
    }

    subscribe(channelName) {
        const channel = new ReverbChannel(this, channelName);
        this.channels.set(channelName, channel);

        if (this.state === "connected") {
            channel.subscribe();
        }

        return channel;
    }

    bind(event, callback) {
        if (!this.callbacks.has(event)) {
            this.callbacks.set(event, []);
        }
        this.callbacks.get(event).push(callback);
    }

    trigger(event, data) {
        const callbacks = this.callbacks.get(event) || [];
        callbacks.forEach((callback) => callback(data));
    }

    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        }
    }
}

class ReverbChannel {
    constructor(client, name) {
        this.client = client;
        this.name = name;
        this.callbacks = new Map();
        this.subscribed = false;
    }

    subscribe() {
        this.client.send({
            event: "pusher:subscribe",
            data: { channel: this.name },
        });
    }

    bind(event, callback) {
        if (!this.callbacks.has(event)) {
            this.callbacks.set(event, []);
        }
        this.callbacks.get(event).push(callback);
    }

    trigger(event, data) {
        const callbacks = this.callbacks.get(event) || [];
        callbacks.forEach((callback) => callback(data));

        if (event === "pusher_internal:subscription_succeeded") {
            this.subscribed = true;
            console.log(`📡 Souscrit au canal: ${this.name}`);
        }
    }
}

export default ReverbClient;
