package p5.educationalstrange.tuple;

import java.io.Serializable;

public class ETokenTuple implements Comparable<ETokenTuple>, Serializable{
	private String text, type;
	private int line, column;
	
	// store the text prior generalisation
	private String rawText;

	public ETokenTuple(String text, String type, int line) {
		this(text, type, line, -1);
	}

	public ETokenTuple(String text, String type, int line, int column) {
		super();
		this.text = text;
		this.rawText = text;
		this.type = type;
		this.line = line;
		this.column = column;
	}

	public void incrementLine() {
		this.line++;
	}

	public String getText() {
		return text;
	}

	public void setText(String text) {
		this.text = text;
	}

	public String getType() {
		return type;
	}

	public void setType(String type) {
		this.type = type;
	}

	public int getLine() {
		return line;
	}

	public void setLine(int line) {
		this.line = line;
	}

	public int getColumn() {
		return column;
	}

	public void setColumn(int column) {
		this.column = column;
	}

	public String getRawText() {
		return rawText;
	}

	public void setRawText(String rawText) {
		this.rawText = rawText;
	}

	@Override
	public int compareTo(ETokenTuple arg0) {
		// TODO Auto-generated method stub
		if (this.getLine() != arg0.getLine())
			return this.getLine() - arg0.getLine();
		else{
			return this.getColumn() - arg0.getColumn();
		}
	}
	
	public String toString(){
		return this.getText();
	}
	
	public static ETokenTuple clone(ETokenTuple o) {
		// return a copy of 'o' object
		ETokenTuple copy = new ETokenTuple(o.getText(), o.getType(), o.getLine(), o.getColumn());
		copy.setRawText(o.getRawText());
		
		return copy;
	}
}
