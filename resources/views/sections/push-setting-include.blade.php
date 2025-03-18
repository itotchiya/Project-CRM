@if ($pushSetting->status == 'active')
    <link rel="manifest" href="{{ asset('manifest.json') }}"/>
    <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
    <script>
        var OneSignal = window.OneSignal || [];
        OneSignal.push(function () {
            OneSignal.SERVICE_WORKER_PARAM = {
                scope: '/vendor/onesignal/'
            };
            OneSignal.SERVICE_WORKER_PATH = 'vendor/onesignal/OneSignalSDKWorker.js'
            OneSignal.SERVICE_WORKER_UPDATER_PATH = 'vendor/onesignal/OneSignalSDKUpdaterWorker.js'
            OneSignal.init({
                appId: "{{ $pushSetting->onesignal_app_id }}",
                autoRegister: false,
                notifyButton: {
                    enable: true,
                    showCredit: false,
                    theme: "inverse",
                    size: "small",
                    position: '{{ isRtl() ? 'bottom-left' : 'bottom-right' }}',
                    text: {
                        'tip.state.unsubscribed': "@lang('app.onesignal.tip.state.unsubscribed')",
                        'tip.state.subscribed': "@lang('app.onesignal.tip.state.subscribed')",
                        'tip.state.blocked': "@lang('app.onesignal.tip.state.blocked')",
                        'message.prenotify': "@lang('app.onesignal.message.prenotify')",
                        'message.action.subscribed': "@lang('app.onesignal.message.action.subscribed')",
                        'message.action.resubscribed': "@lang('app.onesignal.message.action.resubscribed')",
                        'message.action.unsubscribed': "@lang('app.onesignal.message.action.unsubscribed')",
                        'dialog.main.title': "@lang('app.onesignal.dialog.main.title')",
                        'dialog.main.button.subscribe': "@lang('app.onesignal.dialog.main.button.subscribe')",
                        'dialog.main.button.unsubscribe': "@lang('app.onesignal.dialog.main.button.unsubscribe')",
                        'dialog.blocked.title': "@lang('app.onesignal.dialog.blocked.title')",
                        'dialog.blocked.message': "@lang('app.onesignal.dialog.blocked.message')"
                    }
                },
                promptOptions: {
                    /* actionMessage limited to 90 characters */
                    actionMessage: "@lang('app.onesignal.actionMessage')",
                    /* acceptButtonText limited to 15 characters */
                    acceptButtonText: "@lang('app.onesignal.acceptButtonText')",
                    /* cancelButtonText limited to 15 characters */
                    cancelButtonText: "@lang('app.onesignal.cancelButtonText')"
                }
            });

            OneSignal.on('subscriptionChange', function (isSubscribed) {
                console.log("The user's subscription state is now:", isSubscribed);
            });


            if (Notification.permission === "granted") {
                // Automatically subscribe user if deleted cookies and browser shows "Allow"
                OneSignal.getUserId()
                    .then(function (userId) {
                        if (!userId) {
                            OneSignal.registerForPushNotifications();
                        } else {
                            let db_onesignal_id = '{{ $user->onesignal_player_id }}';

                            if (db_onesignal_id !== userId) { //update onesignal ID if it is new
                                updateOnesignalPlayerId(userId);
                            }
                        }
                    })
            } else {
                OneSignal.isPushNotificationsEnabled(function (isEnabled) {

                    OneSignal.getUserId(function (userId) {
                        console.log("OneSignal User ID:", userId);
                        // (Output) OneSignal User ID: 270a35cd-4dda-4b3f-b04e-41d7463a2316
                        let db_onesignal_id = '{{ $user->onesignal_player_id }}';
                        console.log('database id : ' + db_onesignal_id);

                        if (db_onesignal_id !== userId) { //update onesignal ID if it is new
                            updateOnesignalPlayerId(userId);
                        }
                    });

                    if (isEnabled) {
                        console.log("Push notifications are enabled! - 2    ");
                        // console.log("unsubscribe");
                        // OneSignal.setSubscription(false);
                    } else {
                        console.log("Push notifications are not enabled yet. - 2");
                        OneSignal.showNativePrompt();
                        // OneSignal.registerForPushNotifications({
                        //         modalPrompt: true
                        // });
                    }
                });

            }
        });
    </script>
@endif

@if ($pushSetting->beams_push_status == 'active')
    <script src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js" async></script>

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }

    </script>

    <script>
        $(document).ready(function () {
        const currentUserId = "wrkst-{{ user()->id }}"; // Get this from your auth system

        const beamsClient = new PusherPushNotifications.Client({
            instanceId: "{{ push_setting()->instance_id }}",
        });

        const beamsTokenProvider = new PusherPushNotifications.TokenProvider({
            url: "{{ route('dashboard.beam_auth') }}",
        });

        beamsClient.start()
        .then(() => beamsClient.addDeviceInterest('Worksuite'))
        .then(() => beamsClient.setUserId(currentUserId, beamsTokenProvider))
        .then(() => console.log('Successfully registered and subscribed!'))
        .catch(console.error);

        beamsClient
        .getUserId()
        .then((userId) => {
            console.log(userId, currentUserId);
            // Check if the Beams user matches the user that is currently logged in
            if (userId !== currentUserId) {
            // Unregister for notifications
            return beamsClient.stop();
            }
        })
        .catch(console.error);
        });
    </script>
@endif

@if ($pusherSettings->status)
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>

    <script>
        // Enable pusher logging - don't include this in production
        // Pusher.logToConsole = true;

        const pusher = new Pusher('{{ $pusherSettings->pusher_app_key }}', {
            cluster: '{{ $pusherSettings->pusher_cluster }}',
            forceTLS: '{{ $pusherSettings->force_tls }}'
        });

    </script>
@endif
