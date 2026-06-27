import React, { useState, useEffect } from 'react';
import { ticketAPI, replyAPI } from '../api';
import { Link, useNavigate, useParams } from 'react-router-dom';

export default function TicketDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [ticket, setTicket] = useState(null);
  const [replies, setReplies] = useState([]);
  const [newReply, setNewReply] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const fetchTicket = async () => {
    try {
      setLoading(true);
      const response = await ticketAPI.show(id);
      setTicket(response.data.ticket);
      setReplies(response.data.ticket.replies || []);
    } catch (err) {
      setError('Failed to load ticket');
      if (err.response?.status === 403 || err.response?.status === 404) {
        navigate('/dashboard');
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTicket();
  }, [id]);

  const handleSubmitReply = async (e) => {
    e.preventDefault();
    if (!newReply.trim()) return;

    try {
      setSubmitting(true);
      const response = await replyAPI.store(id, { body: newReply });
      setReplies([...replies, response.data.reply]);
      setNewReply('');
      
      // Refresh ticket to get updated reply count
      await fetchTicket();
    } catch (err) {
      setError('Failed to add reply');
    } finally {
      setSubmitting(false);
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'open': return 'bg-green-100 text-green-800';
      case 'in_progress': return 'bg-yellow-100 text-yellow-800';
      case 'resolved': return 'bg-blue-100 text-blue-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getPriorityColor = (priority) => {
    switch (priority) {
      case 'high': return 'bg-red-100 text-red-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'low': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="text-gray-600">Loading ticket...</div>
      </div>
    );
  }

  if (error && !ticket) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <nav className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
          <Link to="/dashboard" className="text-xl font-bold text-gray-900 hover:text-blue-600">
            ← Back to Dashboard
          </Link>
          <button
            onClick={() => {
              localStorage.removeItem('token');
              localStorage.removeItem('user');
              window.location.href = '/login';
            }}
            className="text-sm text-gray-600 hover:text-gray-900"
          >
            Logout
          </button>
        </div>
      </nav>

      <main className="max-w-4xl mx-auto px-4 py-8">
        {error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            {error}
          </div>
        )}

        {ticket && (
          <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div className="flex justify-between items-start mb-4">
              <h1 className="text-2xl font-bold text-gray-900">{ticket.title}</h1>
              <div className="flex gap-2">
                <span className={`px-3 py-1 text-sm font-medium rounded ${getStatusColor(ticket.status)}`}>
                  {ticket.status.replace('_', ' ')}
                </span>
                <span className={`px-3 py-1 text-sm font-medium rounded ${getPriorityColor(ticket.priority)}`}>
                  {ticket.priority}
                </span>
              </div>
            </div>

            <div className="text-gray-600 mb-4">{ticket.description}</div>

            <div className="flex items-center gap-4 text-sm text-gray-500 border-t pt-4">
              <div>
                <span className="font-medium">Created by:</span> {ticket.user?.name}
              </div>
              <div>
                <span className="font-medium">Assigned to:</span> {ticket.assigned_agent?.name || 'Unassigned'}
              </div>
              <div>{formatDate(ticket.created_at)}</div>
            </div>
          </div>
        )}

        <div className="bg-white rounded-lg shadow-sm p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Replies</h2>

          {replies.length === 0 ? (
            <div className="text-center py-8 text-gray-600">
              No replies yet. Be the first to respond!
            </div>
          ) : (
            <div className="space-y-4 mb-6">
              {replies.map((reply) => (
                <div key={reply.id} className="border-b pb-4 last:border-0">
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-2">
                      <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                        {reply.user?.name?.charAt(0) || '?'}
                      </div>
                      <span className="font-medium text-gray-900">{reply.user?.name}</span>
                    </div>
                    <span className="text-sm text-gray-500">{formatDate(reply.created_at)}</span>
                  </div>
                  <div className="text-gray-700 ml-10">{reply.body}</div>
                </div>
              ))}
            </div>
          )}

          <form onSubmit={handleSubmitReply}>
            <div className="mb-4">
              <label htmlFor="reply" className="block text-sm font-medium text-gray-700 mb-2">
                Add a reply
              </label>
              <textarea
                id="reply"
                value={newReply}
                onChange={(e) => setNewReply(e.target.value)}
                rows={4}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Type your reply here..."
                required
              />
            </div>

            <button
              type="submit"
              disabled={submitting || !newReply.trim()}
              className="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
            >
              {submitting ? 'Sending...' : 'Send Reply'}
            </button>
          </form>
        </div>
      </main>
    </div>
  );
}