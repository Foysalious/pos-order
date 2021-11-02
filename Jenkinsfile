pipeline {
    agent any
    stages {
        stage('MAKE ENV FILE') {
            steps {
                withCredentials([
                    string(credentialsId: 'VAULT_ROLE_ID', variable: 'VAULT_ROLE_ID'),
                    string(credentialsId: 'VAULT_SECRET_ID', variable: 'VAULT_SECRET_ID')
                ]) {
                    sh './bin/make_env.sh'
                    sh './bin/parse_env.sh'
                }
            }
        }
        stage('PULL IN DEVELOPMENT') {
            when { branch 'development' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'development-server',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: '',
                                    execTimeout: 120000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: 'pos-order',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: '**/*.env'
                                ),
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/pos-order && ./bin/deploy.sh development',
                                    execTimeout: 300000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
        stage('BUILD FOR PRODUCTION - FOR DOCKER') {
            when { branch 'master' }
            steps {
                 sh './bin/copy_needed_auth.sh'
                 sh './bin/build.sh'
            }
        }
        stage('DEPLOY TO PRODUCTION - FOR PRODUCTION SERVER') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'smanager-sales-server',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/pos-order && ./bin/deploy.sh master',
                                    execTimeout: 300000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
        stage('CLEAN UP BUILD') {
            when { branch 'master' }
            steps {
                sh './bin/remove_build.sh'
            }
        }
        stage('DELETE WORKSPACE FILES') {
            steps {
                echo 'Deleting current workspace ...'
                deleteDir() /* clean up our workspace */
            }
        }
        stage('DELETE DOCKER DANGLING IMAGES') {
            when { branch 'master' }
            steps {
                script {
                    sshPublisher(publishers: [
                        sshPublisherDesc(configName: 'smanager-sales-server',
                            transfers: [
                                sshTransfer(
                                    cleanRemote: false,
                                    excludes: '',
                                    execCommand: 'cd /var/www/pos-order && ./bin/remove_dangling_images.sh',
                                    execTimeout: 300000,
                                    flatten: false,
                                    makeEmptyDirs: false,
                                    noDefaultExcludes: false,
                                    patternSeparator: '[, ]+',
                                    remoteDirectory: '',
                                    remoteDirectorySDF: false,
                                    removePrefix: '',
                                    sourceFiles: ''
                                )
                            ],
                            usePromotionTimestamp: false,
                            useWorkspaceInPromotion: false,
                            verbose: true
                        )]
                    )
                }
            }
        }
    }
}
