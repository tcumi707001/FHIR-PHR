var consent = {
    "resourceType": "Consent",
    "identifier": [{
        "type": {
            "text": "22"
        }
    }],
    "patient": {
        "reference": "Patient/10"
    },
    "performer": {
        "reference": "Patient/10"
    },
    "provision": {
        "actor": {
            "reference": "Practitioner/11"
        },
        "action": {
            "text": "GET"
        },
        "securityLabel": {
            "display": "GET",
            "system": "http://203.64.84.213:8080/hapi-fhir-jpaserver/fhir/MedicationRequest?patient=10&authoredon=gt2020-04-12"
        },
        "data": {
            "meaning": "lt2020-05-25"
        }
    }
}