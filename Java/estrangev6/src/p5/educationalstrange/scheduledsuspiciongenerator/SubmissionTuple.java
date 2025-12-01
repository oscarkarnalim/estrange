package p5.educationalstrange.scheduledsuspiciongenerator;

import java.io.File;

public class SubmissionTuple {
	private int submission_id;
	private String file_path;
	private boolean has_suspicion_report_created;
	private int submitter_id;

	public SubmissionTuple(int submission_id, String file_path, boolean has_suspicion_report_created,
			int submitter_id) {
		super();
		this.submission_id = submission_id;
		this.file_path = file_path.replace('/', File.separatorChar);
		this.has_suspicion_report_created = has_suspicion_report_created;
		this.submitter_id = submitter_id;
	}

	public int getSubmitter_id() {
		return submitter_id;
	}

	public void setSubmitter_id(int submitter_id) {
		this.submitter_id = submitter_id;
	}

	public int getSubmission_id() {
		return submission_id;
	}

	public void setSubmission_id(int submission_id) {
		this.submission_id = submission_id;
	}

	public String getFile_path() {
		return file_path;
	}

	public void setFile_path(String file_path) {
		this.file_path = file_path;
	}

	public boolean isHas_suspicion_report_created() {
		return has_suspicion_report_created;
	}

	public void setHas_suspicion_report_created(boolean has_suspicion_report_created) {
		this.has_suspicion_report_created = has_suspicion_report_created;
	}
}
