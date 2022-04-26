properties([
    buildDiscarder(
        logRotator( artifactDaysToKeepStr: '',
                    artifactNumToKeepStr: '',
                    daysToKeepStr: '',
                    numToKeepStr: '5' )
    ),
    parameters([
        string( defaultValue: 'master',
                description: 'Branch for git repo<br/>Examples: master, staging, hotfix-123, issue-999',
                name: 'Branch_name',
                trim: false ),
        booleanParam( defaultValue: false,
                      description: 'Skip OWASP Dependency Check',
                      name: 'skip_OWASP' ),
        booleanParam( defaultValue: false,
                      description: 'Skip SonarQube security check',
                      name: 'skip_Sonar' ),
        booleanParam( defaultValue: false,
                      description: 'It will skip rpm building and use the latest builded rpm. Usefull for fast redeploying.<br/><b>DO NOT USE IT IF PREVIOUS BUILD FAILED!</b>',
                      name: 'skip_Build' )
    ])
])
node() {
    def GIT_URL = scm.userRemoteConfigs[0].url
    stage('Wipe workspace') {
        if (params.skip_Build == false) {
            cleanWs cleanWhenFailure: false, cleanWhenNotBuilt: false, cleanWhenUnstable: false
        } else { echo 'Nothing to do' }
    }
    stage('Pulling code') {
        if (params.skip_Build == false) {
            git branch: "${Branch_name}", url: GIT_URL
        } else { echo 'Nothing to do' }
    }
    stage('Download deps'){
        if (params.skip_Build == false) {
            echo 'Put your dowload steps code here.'
        } else { echo 'Nothing to do' }
    }
    stage('Build'){
        if (params.skip_Build == false) {
            echo 'Put your build steps code here.'
        } else { echo 'Nothing to do' }
    }
    stage("Dependency Check") {
        if (params.skip_Build == false && params.skip_OWASP == false) {
            dependencyCheck additionalArguments: '--out ./ --scan admin --scan catalog --exclude cicd/** --disableNodeJS --disableNodeAudit --disableAssembly --proxyserver proxy.cardpay-aws.net --proxyport 3128 --format ALL', odcInstallation: 'dependency-check 6.0.3'
            dependencyCheckPublisher pattern: '', failedTotalCritical: '1', failedTotalHigh: '1', failedTotalMedium: '1', unstableTotalLow: '1'
        } else { echo 'Nothing to do' }
    }
    stage('Security check'){
        if (params.skip_Build == false && params.skip_Sonar == false) {
            def scannerHome = tool 'Sonar Runner';
            withSonarQubeEnv {
                dir('volumes') {
                    sh 'mkdir sources-to-check && cp -rpv ${WORKSPACE}/{admin,catalog} sources-to-check/'
                    sh 'cp -f ${WORKSPACE}/cicd/sonar-project.properties ./sonar-project.properties'
                    sh "${scannerHome}/bin/sonar-scanner"
                }
            }
        } else { echo 'Nothing to do' }
    }
    stage('Build RPM'){
        if (params.skip_Build == false) {
            def RPM_NAME = "cardpay-cms-plugin-opencart"
            env.RPM_NAME = RPM_NAME
            echo "Put RPM build steps code here"

        } else { echo 'Nothing to do' }
    }
    stage('Save rpm to repo'){
       echo 'Put your RPM publishing steps code here'
    }
    stage('Deploy'){
        echo 'Put your deploy steps code here'
    }
}
