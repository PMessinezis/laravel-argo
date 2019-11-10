<?php


return [

    /** any defined global-flag will be applied on all calls to argo cli */
    'global-flags' => [

        /**Username to impersonate for the operation */
        'as' => null ,

        /**Group to impersonate for the operation, this flag can be repeated to specify multiple groups. */
        'as-group' => [] ,

        /**Path to a cert file for the certificate authority */
        'certificate-authority' => null ,

        /**Path to a client certificate file for TLS */
        'client-certificate' => null ,

        /**Path to a client key file for TLS */
        'client-key' => null ,

        /**The name of the kubeconfig cluster to use */
        'cluster' => null ,

        /**The name of the kubeconfig context to use */
        'context' => null ,

        /** If true, the server's certificate will not be checked for validity. */
        /** This will make your HTTPS connections insecure */
        'insecure-skip-tls-verify' => null,

        /** Path to a kube config. Only required if out-of-cluster */
        'kubeconfig' => null ,

        /** If present, the namespace scope for this CLI request */
        'namespace' => null ,

        /** Password for basic authentication to the API server */
        'password' => null ,

        /** The length of time to wait before giving up on a single server request. */
        /** Non-zero values should contain a corresponding time unit (e.g. 1s, 2m, 3h). */
        /** A value of zero means don't timeout requests. (default "0") */
        'request-timeout' => null ,

        /** The address and port of the Kubernetes API server */
        'server' => null ,

        /** Bearer token for authentication to the API server */
        'token' => null ,

        /** The name of the kubeconfig user to use */
        'user' => null ,

        /** Username for basic authentication to the API server */
        'username' => null ,

    ],

];
