import pytest
from app import app

@pytest.fixture
def client():
    app.config['TESTING'] = True
    with app.test_client() as client:
        yield client

def test_hello_world(client):
    response = client.get('/')
    assert response.status_code == 200
    assert b'Hello from the backend!' in response.data

def test_generate_presentation_no_text(client):
    response = client.post('/generate_presentation', json={})
    assert response.status_code == 400
    assert b'No text provided' in response.data

def test_generate_presentation_with_text(client):
    # This test will require mocking the OpenAI API call
    # For now, we'll just test the basic structure
    response = client.post('/generate_presentation', json={'text': 'Test input text'})
    assert response.status_code == 200
    data = response.get_json()
    assert 'title' in data
    assert 'slides' in data
    assert isinstance(data['slides'], list)
